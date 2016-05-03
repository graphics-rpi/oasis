var Stroke_List = [];
var Prev_Combos_List = [];
var Object_List = [];
var items = [];
var cornerpoints = [];
var cornercount = 0;
var objidcount = 0;
var lastprocessed = 0;

//simplifyratio of around .5 to 2
//n is the max length before you want to allocate more points
//pps = points per segment
//aka the resample size grows as the stroke is longer
function Stroke(id, idnum, pts, resampleSize, type){
	this.id = id;
	this.idnum = idnum;
    this.type = type;
	this.length = strokeLength(pts);
	//this.points = Resample(pts, resampleSize);
	this.points = pts;
	this.numPoints = this.points.length;
	this.center = centroid(pts);
	this.corners = shortStraw(this.points);
	this.cornerIds = createCornerMarkers(this.corners);
	this.bestFitLine = leastSquares(this.points);
    this.removed = false;
    this.windows = [];
    this.scores = [];
}

function StrokeCompare(str1, str2, pts){
	this.stroke = str1;
	this.otherStroke = str2;
	this.otherPoints = pts;
	this.timeDist = math.abs(str1.idnum - str2.idnum);
	this.eucDist = distance(str1.center, str2.center);
	this.score = this.timeDist + math.round(this.eucDist/100);
}

function Primitive(ids, name, score){
	this.ids = ids;
	this.pts = combineStrokes(ids);
	this.name = name;
	this.score = score;
	this.corners = findCorners(this.pts);
	this.allCorners = shortStraw(this.pts);
	this.cornerIds = createCornerMarkers(this.corners);
	this.cornerIds2 = createCornerMarkers(this.allCorners);
	this.center = centroid(this.pts);
	this.cdistance = avg_cdistance(cdistance(this.pts));
}

function PaperObject(id, name, strokes, center, corners, cdist){
	this.id = id;
	this.name = name;
	this.strokes = strokes;
	this.center = center;
	this.corners = corners;
	this.cdist = cdist;
}

function ObjectTemplate(name, primitives){
	this.name = name;
	this.primitives = primitives;
}

function strokeLength(pts){
	var sum = 0;
	for(var i=0; i<pts.length-1; i++)
		sum += distance(pts[i], pts[i+1]);
	return sum;
}

function calcResize(length, resampleSize, lengPerSegement){
	return math.round(length/lengPerSegement)*resampleSize;
}

//if p1 is a better fit than p2
function pointscore(p1, p2, x, y){
	if((p1.x*x + p1.y*y) > (p2.x*x + p2.y*y))
		return true;
	return false;
}

//the center point of all points entered
function centroid(points){
	var sx=0, sy=0;
	for(var i=0; i<points.length; i++){
		sx = sx+points[i].x;
		sy = sy+points[i].y;
	}
	var avgx = sx / points.length;
	var avgy = sy / points.length;
	
	return new Point(avgx, avgy);
}

function leastSquares(pts) {
    var x = [], y = [];
    for(var i=0; i<pts.length; i++){
        x.push(pts[i].x);
        y.push(pts[i].y);
    }
    var lr = {};
    var n = y.length;
    var sum_x = 0, sum_y = 0;
    var sum_xy = 0, sum_xx = 0;
    var sum_yy = 0;
     
    for (var i = 0; i < y.length; i++) {
        sum_x += x[i];
        sum_y += y[i];
        sum_xy += (x[i]*y[i]);
        sum_xx += (x[i]*x[i]);
        sum_yy += (y[i]*y[i]);
    }
    lr['slope'] = ((n*sum_xy) - (sum_x*sum_y)) / ((n*sum_xx) - (sum_x*sum_x));
    if(!isFinite(lr['slope']))
    	lr['slope'] = 999999;
    if(lr['slope'] == 0)
    	lr['slope'] = .00001;
    lr['intercept'] = (sum_y - (lr.slope * sum_x))/n;
    lr['r2'] = Math.pow( ( (n*sum_xy) - (sum_x*sum_y) ) /
    	Math.sqrt(( (n*sum_xx) - (sum_x*sum_x) ) * ( (n*sum_yy) - (sum_y*sum_y) )),2);
    return lr;
}

//distances between the centroid and all the points
function cdistance(points){
	var c = centroid(points);
	var distances = [];
	var t;
	for(var i=0; i<points.length; i++){
		t = distance(points[i], c);
		distances.push(t);
	}
	return distances;
}

function avg_cdistance(distances){
	var sum = 0;
	for(var i=0; i<distances.length; i++){
		sum = sum+distances[i];
	}
	return (sum/distances.length);
}

//only works to find 4 corners
function findCorners(stroke){
	var p = stroke;
	
	var botR = p[0], topR = p[1], topL = p[2], botL = p[3];

	for(var i=4; i<p.length; i++){
		//top left
		if(pointscore(p[i], topL, -1.5, -1)){
			topL = p[i];
		}
		//bot right
		if(pointscore(p[i], botR, 1.5, 1)){
			botR = p[i];
		}

		//top right
		if(pointscore(p[i], topR, 1, -1.5)){
			topR = p[i];
		}

		//bot left
		if(pointscore(p[i], botL, -1, 1.5)){
			botL = p[i];
		}
	}
	return [topL, topR, botR, botL];
}

//rotates point (x,y) around center point at angle
function rotatepoint(cx, cy, x, y, angle){
	// x, y are coordinates of a corner point
	// translate point to origin
	var tempX = x - cx;
	var tempY = y - cy;

	// now apply rotation
	var rotatedX = tempX*math.cos(angle) - tempY*math.sin(angle);
	var rotatedY = tempX*math.sin(angle) + tempY*math.cos(angle);

	// translate back
	x = rotatedX + cx;
	y = rotatedY + cy;

	return new Point(x, y);
}

//find angle between 2 points with respect to origin
function angle2Points(p1, p2){
	return Math.atan2(p2.y - p1.y, p2.x - p1.x) * 180 / Math.PI;
}

//angle between 2 points at a fixed point
function angle2PointsFixedPoint(p1, p2, fixed){
	var a1 = angle2Points(p1, fixed);
	var a2 = angle2Points(p2, fixed)
	return math.abs(a1-a2);
}

//original vs the one you are comparing to
function similarityScore(org, comp){
	if(org.length != comp.length)
		return [];
	var arr = [];
	for(var i=0; i<org.length; i++){
		var s = distance(org[i], comp[i]);
		arr.push(s);
	}
	return arr;
}

function sumArray(a){
	var sum = 0;
	for(var i=0; i<a.length; i++){
		sum = sum + a[i];
	}
	return sum;
}

//combines strokes into one stroke
function combineStrokes(idnums){
	var stroke = [];
	for(var i=0; i<idnums.length; i++){
		var st = Stroke_List[idnums[i]].points;
		for(var j=0; j<st.length; j++){
			stroke.push(st[j]);
		}
	}
	return stroke;
}

//finds strokes to process
function findStrokesFast(strokelist, futurenum, lastprocessed){
	var indicies = [];
	var count = 0;
	while((count < futurenum && (strokelist.length-count)>0) ||
		(strokelist.length-count-1) >= lastprocessed){
		if(strokelist[strokelist.length-count-1].type != 'window')
			indicies.push(strokelist.length-count-1);
		count++;
	}
	return indicies.sort(function(a, b){return a-b});
}

//finds all the combinations of a set of numbers
//returns in string form ex. "1 2 3"
function getCombinations(chars) {
  var result = [];
  var f = function(prefix, chars) {
    for (var i = 0; i < chars.length; i++) {
      result.push(prefix + chars[i]);
      f(prefix + chars[i] + " ", chars.slice(i + 1));
    }
  }
  f('', chars);
  return result;
}

//turns strings into an array of numbers
function indiciesFromString(str){
	var output = str.split(" ");
	for(var i=0; i<output.length; i++){
		output[i] = parseInt(output[i]);
	}
	return output;
}

//finds powerset minus the powerset of individuals other than itself 
function powerSet(ind){
	var results = getCombinations(ind);
	var output = [];
	for(var i=0; i<results.length; i++){
		var a = indiciesFromString(results[i]);
		if(a.length == 1){
			output.push(a);
		}
		else
			output.push(a);
	}
	return output;
}

/////////////////////////////////////////////////////////////////
function combineCombinations2(subsets){
	var output = [];
	for(var i=0; i<subsets.length; i++){
		var c = getCombinations2(subsets[i]);
		for(var j=0; j<c.length; j++)
			output.push(c[j]);
	}
	return output;
}

//finds all the combinations of a set of numbers
//returns in string form ex. "1 2 3"
function getCombinations2(chars) {
  var result = [];
  var f = function(prefix, chars) {
    for (var i = 0; i < chars.length; i++) {
      result.push(prefix + chars[i]);
      f(prefix + chars[i] + " ", chars.slice(i + 1));
    }
  }
  f('', chars);
  return result;
}

//turns strings into an array of numbers
function indiciesFromString2(str){
	var output = str.split(" ");
	for(var i=0; i<output.length; i++){
		output[i] = parseInt(output[i]);
	}
	return output;
}

//if you get a lot of indicies, break them up into num sized arrays
function createSubsets2(arr, futurenum){
	var output = [];
	var temp = [];
	if(arr.length <= futurenum)
		output.push(arr)
	else{
		for(var i=0; i<arr.length-futurenum; i++){
			for(var j=i; j<i+futurenum; j++){
				temp.push(arr[j]);
			}
			output.push(temp.slice());
			temp.splice(0, temp.length);
		}
	}
	return output;
}

//finds powerset minus the powerset of individuals other than itself 
function powerSet2(ind, futurenum){
	var s = createSubsets2(ind, futurenum);
	var results = combineCombinations2(s);
	var output = [];
	for(var i=0; i<results.length; i++){
		var a = indiciesFromString2(results[i]);
		if(a.length == 1){
			output.push(a);
		}
		else
			output.push(a);
	}
	return output;
}
/////////////////////////////////////////////////////////////////////////////////////



//finds some number of combinations into the future and scores them, returns them
function history_combinations_fast(futurenum, prevPrimitives){
	var combos = [],
		currentindicies = [],
		currentstrokes = [],
		futurestrokes = [];
	var dollar = new NDollarRecognizer(true);

	futurestrokes = findStrokesFast(Stroke_List, futurenum, lastprocessed);
	futurestrokes = powerSet2(futurestrokes, futurenum);

	for(var a=0; a<futurestrokes.length; a++){
		currentstrokes = [];
		currentindicies = [];
		for(var b=0; b<futurestrokes[a].length; b++){
			hist_index = futurestrokes[a][b];
			currentindicies.push(hist_index);
			currentstrokes.push(Stroke_List[hist_index].points);
		}
		var result = dollar.Recognize(currentstrokes, true, false, true);
		prevPrimitives.push({name:result.Name, score:result.Score, currentindicies:currentindicies});
	}
	prevPrimitives.sort(function(a, b){
		return b.score - a.score;
	});

	lastprocessed = Stroke_List.length-1;
	return prevPrimitives;
}

function binarySearch(array, key) {
    var lo = 0,
        hi = array.length - 1,
        mid,
        element;
    while (lo <= hi) {
        mid = Math.floor((lo + hi) / 2, 10);
        element = array[mid];
        if (element < key) {
            lo = mid + 1;
        } else if (element > key) {
            hi = mid - 1;
        } else {
            return mid;
        }
    }
    return -1;
}

function binarySearchPaperId(array, key) {
    var lo = 0,
        hi = array.length - 1,
        mid,
        element;
    while (lo <= hi) {
        mid = Math.floor((lo + hi) / 2, 10);
        element = array[mid].id;
        if (element < key) {
            lo = mid + 1;
        } else if (element > key) {
            hi = mid - 1;
        } else {
            return mid;
        }
    }
    return -1;
}

//chooses the top scoring combinations of strokes
function choosePrimitivesFast(history, newScores){
	var i=0;
	var temp = history;
	var results = [];
	var toadd = [];
	var matched = [];
	var chosen = true;
	//match every line to something
	while(matched.length != temp.length && i < newScores.length){
		//go through all possibilities, sorted by score
		//read through each possibility's line dependencies
		//var indicies = newScores[i].currentindicies;
		var indicies;
		if(newScores[i] instanceof Primitive){
			indicies = newScores[i].ids;
		}
		else {
			indicies = newScores[i].currentindicies;
		}		
		var combined_strokes = combineStrokes(indicies);
		var corners = findCorners(combined_strokes);

		for(var j=0; j<indicies.length; j++){
			var index = binarySearch(matched, indicies[j]);
			if(index == -1){
				toadd.push(indicies[j]);
			}
			//oh no we need to undo what we did this loop
			else{
				toadd.splice(0, toadd.length);
				break;
			}
		}
		if(toadd.length != 0){
			//results.push(scores[i]);
			if(newScores[i] instanceof Primitive){
				results.push(newScores[i])
			}
			else{
				results.push(new Primitive(newScores[i].currentindicies, newScores[i].name, newScores[i].score));
			}
		}
		for(var k=0; k<toadd.length; k++)
			matched.push(toadd[k]);

		matched.sort(function(a, b){return a-b});
		toadd.splice(0, toadd.length);
		i++;
	}
	return results;
}

function diagonalDistanceTest(primitive){
	var corners = primitive.corners;
	if(corners.length != 4)
		return -1;
	var sumpercent = 0;
	var d1 = distance(corners[0], corners[2]);
	var d2 = distance(corners[1], corners[3]);
	var avg = (d1+d2)/2;
	sumpercent += math.abs((d1 - avg)/avg);
	sumpercent += math.abs((d2 - avg)/avg);
	sumpercent = sumpercent/2;
	return sumpercent;
}

function rightCornersTest(primitive){
	var corners = primitive.corners;
	if(corners.length != 4)
		return -1;
	var sumpercent = 0;
	var angles = [];

	for(var i=0; i<corners.length; i++)
		angles.push(angle2PointsFixedPoint(corners[i], corners[(i+1)%4], corners[(i+2)%4]));

	var avg = sumArray(angles)/4;
	
	for(var i=0; i<angles.length; i++)
		sumpercent += math.abs((angles[i] - avg)/avg);

	sumpercent = sumpercent/4;
	return sumpercent;
}

//splits a primitive into multiple primitives without names
//for when a primitive doesn't pass previous checks
function splitPrimitive(primitive){
	var output = [];
	for(var i=0; i<primitive.ids.length; i++){
		output.push(new Primitive([primitive.ids[i]], "none"));
	}
	return output;
}

function findStrokesFromIds(ids){
	var output = [];
	for(var i=0; i<ids.length; i++){
		var st = Stroke_List[ids[i]];
		if(st != -1)
			output.push(st);
		else
			console.log("ERROR could not find stroke: findStrokes ", 'stroke_'+ids[i]);
	}
	return output;
}

//checks if a set of strokes is close enough (are the centers within the largest )
function strokesCloseEnough(ids){
	var strokes = findStrokesFromIds(ids);
	var maxHalfLen = 0;
	for(var i=0; i<strokes.length; i++){
		if(maxHalfLen < (strokes[i].length/2))
			maxHalfLen = (strokes[i].length/2);
	}
	for(var i=0; i<strokes.length-1; i++){
		for(var j=i+1; j<strokes.length; j++){
			if(distance(strokes[i].center, strokes[j].center) > maxHalfLen)
				return false;
		}
	}
	return true;
}

//after getting list of primitives, apply some checks
//make sure they are actually squares, rects, etc.
function refinePrimitives(primitives){
	var new_primitives = [],
		threshold = .15;
	for(var i=0; i<primitives.length; i++){
		var curr_primitive = primitives[i];

		if(curr_primitive.name == "rect" || curr_primitive.name == "rectL"){
			if(diagonalDistanceTest(curr_primitive) < threshold &&
				curr_primitive.allCorners.length > 2 &&
				curr_primitive.allCorners.length < 10){
				new_primitives.push(curr_primitive);
			}
			else{
				// var temp = splitPrimitive(curr_primitive);
				// for(var k=0; k<temp.length; k++)
				// 	new_primitives.push(temp[k]);
			}
		}
		else if(curr_primitive.name == "D"){
			if(strokesCloseEnough(curr_primitive.ids)){
				new_primitives.push(curr_primitive);
			}
		}
		else if(curr_primitive.name == "B"){
			if(strokesCloseEnough(curr_primitive.ids)){
				new_primitives.push(curr_primitive);
			}		
		}
		else if(curr_primitive.name == "W"){
			if(curr_primitive.allCorners.length < 7 && curr_primitive.allCorners.length > 3
				&& strokesCloseEnough(curr_primitive.ids)){
				new_primitives.push(curr_primitive);
			}
		}
		else{
			console.log("ERROR: Primitive not recognized.");
		}
	}
	return new_primitives;
}

function createObjectId(objName){
	var oId = objName + '_' + objidcount;
    objidcount++;
    return oId;
}

function drawQuad(x,y,h,w,angle,color,id){
	var rect = paper.rect(x, y, h, w);
    rect.rotate(angle);
    rect.attr({fill:color, "opacity": .5});
    rect.toBack();
    rect.id = id;
}

function drawRectangle(center, corners, color, id){
	if(corners.length != 4)
		return "ERROR";

    var h = (distance(corners[0], corners[1]) + distance(corners[2], corners[3]))/2;
    var w = (distance(corners[1], corners[2]) + distance(corners[3], corners[0]))/2;
    var angle = reverseRotate(corners, center, h, w);

    console.log("[0]", math.round(corners[0].x), math.round(corners[0].y), "[1]", math.round(corners[1].x), math.round(corners[1].y),
    	"[2]", math.round(corners[2].x), math.round(corners[2].y), "[3]", math.round(corners[3].x), math.round(corners[3].y));
    console.log("H", math.round(h), "W", math.round(w), "C", math.round(center.x), math.round(center.y), "A", angle);
    //var angle = leastSquares(corners)['slope'];
    //draw square
    drawQuad(center.x-(w/2), center.y-(h/2), w, h, angle, color, id)
}

function rectDistance(centerx, centery, width, height, px, py) {
    var dx = Math.max((centerx-(width/2)) - px, 0, px - (centerx+(width/2)));
    var dy = Math.max((centery-(height/2)) - py, 0, py - (centery+(height/2)));
    return Math.sqrt(dx*dx + dy*dy);
}

//rect will be rect:{cx, cy, w, h}
function rectScore(points, rect){
    var sum = 0; 
    for(var i=0; i<points.length; i++){
        sum += rectDistance(rect.cx, rect.cy, rect.w, rect.h, points[i].x, points[i].y);
    }
    return sum;
}

function recursiveScoring(points, rect, prevScore){

}

function bestFitRect(object){
    
}

function drawRectangle2(object){

}

function trimPrimitives(primitives){
	var output = [];
	for(var i=0; i<primitives.length; i++){
		if(primitives[i].name != "none"){
			output.push(primitives[i]);
		}
	}
	return output;
}

var ObjectTemplates = [];
ObjectTemplates.push(new ObjectTemplate("wardrobe", ["rect", "W"]));
ObjectTemplates.push(new ObjectTemplate("bed", ["rect", "B"]));
ObjectTemplates.push(new ObjectTemplate("desk", ["rect", "D"]));

function templatesEqual(a1){
	a1.sort();
	for(var i=0; i<ObjectTemplates.length; i++){
		var curr = ObjectTemplates[i].primitives;
		curr.sort();
		var t = curr.toString();
		if(a1.toString() == t)
			return i;
	}
	return -1;
}

//check if 2 primitives match anything in objecttemplates
function checkObjectTemplates(p1, p2){
	var arr = [p1.name, p2.name];
	var templateIndex = templatesEqual(arr);
	if(templateIndex != -1){
		return ObjectTemplates[templateIndex].name;
	}
	return "";
}

function primitiveCompare(p1, p2){
	if(distance(p1.center, p2.center) < math.abs(p1.cdistance - p2.cdistance))
		return true;
	return false;
}

//return true if they are the same
function arrayEquality(a1, a2){
	if(a1.length != a2.length)
		return false;
	for(var i=0; i<a1.length; i++){
		if(a1[i] != a2[i])
			return false;
	}
	return true;
}

//if they are the s
function sameValues(primitive, paperObj, name){
	//if the centers are the same, and corners are same, return true
	if(paperObj.name == name) {
		if(primitive.center == paperObj.center){
			if(arrayEquality(primitive.corners, paperObj.corners) == true)
				return true;
		}
	}
	return false;
}

function newObject(primitive, paperObjList, name){
	for(var i=0; i<paperObjList.length; i++){
		if(sameValues(primitive, paperObjList[i], name) == true)
			return i;
	}
	return -1;
}

function primitivesToObjects(primitives){
	//remove the 'nones'
	//var trimmed = trimPrimitives(primitives);
	var trimmed = primitives.slice(0);
	var	objects = [];
	var added = false;
	for(var i=0; i<(trimmed.length-1); i++){
		for(var j=i+1; j<trimmed.length; j++){
			var firstObj = trimmed[i];
			var secondObj = trimmed[j];
			//do the two match anything in templates
			var templateName = checkObjectTemplates(firstObj, secondObj);
			if(templateName != ""){
				//are the two close at all
				var isClose = primitiveCompare(firstObj, secondObj);
				if(isClose == true){
					//add in the new object
					var chosenObj;
					if(firstObj.name == "rect")
						chosenObj = firstObj;
					else if(secondObj.name == "rect")
						chosenObj = secondObj;

					var index = newObject(chosenObj, Object_List, templateName);
					//this is a brand new object
					if(index == -1){
						var oId = createObjectId(templateName);
						var pObj = new PaperObject(oId, templateName,
							[firstObj.ids, secondObj.ids], chosenObj.center, chosenObj.corners, chosenObj.cdistance);
						objects.push(pObj);
						Object_List.push(pObj);
					}
					//we've created this in the past
					else{
						objects.push(Object_List[index]);
					}

					//delete the future one to prevent weird stuff 
					trimmed.splice(j, 1);
					added = true;
				}
			}
		}
		if(added == false){
			//objects.push(firstObj);
		}
		added = false;
	}
	return objects;
}

function deleteOldObjects(oldObjects, newObjects){
	var toDelete = arrayDifference(oldObjects, newObjects);
	for(var i=0; i<toDelete.length; i++){
		//delete obj from paper
		var obj = toDelete[i];
		var pObj = paper.getById(obj.id);
        pObj.remove();
		//delete obj from paperobj list
		var index = binarySearchPaperId(Object_List, obj.id);
		if(index != -1){
			Object_List .splice(index, 1);
		}
		else{
			console.log("Error Delete Old Objects: Object Not Found");
		}
	}
}

function placeNewObjects(oldObjects, newObjects){
	var toAdd = arrayDifference(newObjects, oldObjects);
	for(var i=0; i<toAdd.length; i++){
		var obj = toAdd[i];
		if(obj.name == "wardrobe"){
			drawRectangle(obj.center, obj.corners, "#FF00FF", obj.id);
		}
		else if(obj.name == "bed"){
			drawRectangle(obj.center, obj.corners, "#FFFF00", obj.id);
		}
		else if(obj.name == "desk"){
			drawRectangle(obj.center, obj.corners, "#00FFFF", obj.id);
		}
	}
}

function theSamePaperObject(p1, p2){
	if(p1.center == p2.center){
		if(p1.cdist == p2.cdist){
			if(arrayEquality(p1.corners, p2.corners)){
				return true;
			}
		}
	}
	return false;
}

//is p1 (same numbers) in arr somewhere
function isIndexOf(p1, arr){
	for(var i=0; i<arr.length; i++){
		if(theSamePaperObject(p1, arr[i]) == true)
			return i;
	}
	return -1;
}

//returns array elements that are present in BEFORE but not AFTER
function arrayDifference(before, after) {
	var result = [];
	for (var i = 0; i < before.length; i++) {
		if (after.indexOf(before[i]) === -1) {
		  result.push(before[i]);
		}
	}
	return result;
}

function cornerDistances(corners){
	var output =[];
	for(var i=0; i<corners.length; i++){
		output.push(distance(corners[i], corners[(i+1)%corners.length]));
	}
	return output;
}

//tests for equality of length of sides
function equalityTest(corners){
	var sum = 0, sumpercent=0;
	var cornerdists = cornerDistances(corners);
	for(var i=0; i<cornerdists.length; i++)
		sum += cornerdists[i];
	var avg = sum/cornerdists.length;
	for(var i=0; i<cornerdists.length; i++){
		var p = math.abs((cornerdists[i] - avg)/avg);
		sumpercent += p;
	}
	sumpercent = sumpercent/corners.length;
	//console.log("PERCENT ", sumpercent);
	return sumpercent;
}

function diagonalEqualityTest(corners){
	if(corners.length != 4)
		return -1;
	var sumpercent = 0;
	var d1 = distance(corners[0], corners[2]);
	var d2 = distance(corners[1], corners[3]);
	var avg = (d1+d2)/2;
	sumpercent += math.abs((d1 - avg)/avg);
	sumpercent += math.abs((d2 - avg)/avg);
	sumpercent = sumpercent/2;
	return sumpercent;
}

function findAllCorners(stroke){
	var angles = [], output = [];
	for(var i=0; i<(stroke.length); i++){
		angles.push(angle2Points(stroke[i], stroke[(i+1)%stroke.length]));
	}
	for(var i=1; i<angles.length+2; i++){
		if(math.abs(math.abs(angles[(i)%angles.length]) - math.abs(angles[(i-1)%angles.length])) > 30){
			output.push(i%angles.length);
		}
	}
	return output;
}

function breakStroke(stroke){
	var corners = findAllCorners(stroke);
	var output = [];
	for(var i=0; i<corners.length-1; i++){
		output.push([stroke[corners[i]], stroke[corners[i+1]]]);
	}
	return output;
}




function drawpointmarker(x, y, color, id){
    var a = paper.circle(x, y, 3);
    a.attr({"stroke": color, "stroke-width": 2});
    a.id = id;
    a.hide();
    //cornerpoints.push(new CornerInfo(cId));
}

function createCornerMarkers(corners){
	var cornerIds = [];
	for(var i=0; i<corners.length; i++){
		var cId = 'marker_' + cornercount;
		cornerIds.push(cId);
    	cornercount++;
    	drawpointmarker(corners[i].x, corners[i].y, "#FF00FF", cId);
	}
	return cornerIds;
}

function showCorners(ds){
	for(var i=0; i<ds.length; i++){
		for(var j=0; j<ds[i].cornerIds.length; j++){
			var marker = paper.getById(ds[i].cornerIds[j]);
			if(marker.node.style.display !== 'none')
				marker.hide();
			else
				marker.show();
		}
	}
}

//this is shortstraw corners
function showCorners2(ds){
	for(var i=0; i<ds.length; i++){
		for(var j=0; j<ds[i].cornerIds2.length; j++){
			var marker = paper.getById(ds[i].cornerIds2[j]);
			if(marker.node.style.display !== 'none')
				marker.hide();
			else
				marker.show();
		}
	}
}


function deleteObjects(){
    for(var i=Object_List.length-1; i>=0; i--){
        var obj = paper.getById(Object_List[i].id);
        obj.remove();
        Object_List.pop();
    }
}

function deleteCorners(){
    for(var i=cornerpoints.length-1; i>=0; i--){
        var obj = paper.getById(cornerpoints[i].id);
        cornerpoints.pop();
        obj.remove();
    }
}

function processObject(primitive, name, objId){
	Object_List.push(new PaperObject(objId, name,
    	primitive.center, primitive.corners, primitive.cdist));
}

function todegrees(n){
	return n*(180/Math.PI);
}

function rotateCorner(corn, center, t){
	var x = corn[0];
	var y = corn[1];
	var cx = center[0];
	var cy = center[1];
	var theta = todegrees(t);

	var tempX = x - cx;
	var tempY = y - cy;

	// now apply rotation
	var rotatedX = tempX*math.cos(theta) - tempY*math.sin(theta);
	var rotatedY = tempX*math.sin(theta) + tempY*math.cos(theta);

	// translate back
	x = rotatedX + cx;
	y = rotatedY + cy;

	return [x,y];
}

function midpoint(p1, p2){
	return {x:(p1.x+p2.x)/2, y:(p1.y+p2.y)/2};
}

function reverseRotate(corners, center, h, w){
	//console.log(corners[0].x, corners[0].y, corners[1].x, corners[1].y, corners[2].x, corners[2].y, corners[3].x, corners[3].y)
	var edge1, edge2;
	if(distance(midpoint(corners[0], corners[1]), midpoint(corners[2], corners[3])) < 
		distance(midpoint(corners[0], corners[3]), midpoint(corners[1], corners[2]))) {
		edge1 = midpoint(corners[0], corners[1]);
		edge2 = midpoint(corners[2], corners[3]);
	}
	else{
		edge1 = midpoint(corners[0], corners[3]);
		edge2 = midpoint(corners[1], corners[2]);
	}

	var ans = angle2Points(midpoint(corners[0], corners[1]), midpoint(corners[2], corners[3]));
	// var ans = angle2Points(edge1, edge2);
	// var linepath = pointsToPath(edge1, edge2);
 //    var drawn_line = paper.path(linepath);

	//var ans = leastSquares(corners);

	// return math.atan(ans['slope']);
	return ans;
}

function outputStrokes(){
	var output = [];
	for(var i=0; i<Stroke_List.length; i++){
		output.push(Stroke_List[i].points);
	}
	return JSON.stringify(output);
}

function reprocessCanvas(strokelist, primitives){
	// console.log(1.1);
    ndollar_results = history_combinations_fast(5, primitives);
    // console.log(1.2);
    primitives = choosePrimitivesFast(Stroke_List, ndollar_results);
    // console.log(1.3);
    refined = refinePrimitives(primitives);
    newObjs = primitivesToObjects(refined);

    return {'ndollar_results':ndollar_results, 'primitives':primitives,
			'refined':refined, 'newObjs':newObjs};
}

function printEverything(results){
	var output = '', arr = results.ndollar_results;
    for (var p in arr) {
        if(arr[p] instanceof Primitive)
            output += arr[p].name+' '+JSON.stringify(arr[p].ids)+'<br>'+arr[p].score+'<br>';
        else
            output += arr[p].name+' '+JSON.stringify(arr[p].currentindicies)+'<br>'+arr[p].score+'<br>';
    }
    document.getElementById('four').innerHTML =  output;

    output = '';
    for (var p in primitives) {
        output += primitives[p].name+' '+ primitives[p].ids+'<br> ';
    }
    document.getElementById('one').innerHTML =  output;

    output = '', arr = results.refined;
    for (var p in arr) {
        output += arr[p].name+' '+ arr[p].ids+'<br> ';
    }
    document.getElementById('two').innerHTML =  output;

    output = '', arr = results.newObjs;
    for (var p in arr) {
        output += arr[p].name+' '+ arr[p].id+'<br> ';
    }
    document.getElementById('three').innerHTML =  output;
}

var combine = function(a, min) {
    var fn = function(n, src, got, all) {
        if (n == 0) {
            if (got.length > 0) {
                all[all.length] = got;
            }
            return;
        }
        for (var j = 0; j < src.length; j++) {
            fn(n - 1, src.slice(j + 1), got.concat([src[j]]), all);
        }
        return;
    }
    var all = [];
    for (var i = min; i < a.length; i++) {
        fn(i, a, [], all);
    }
    all.push(a);
    return all;
}

function scoreStrokes(strokes){
	for(var i=0; i<strokes.length; i++){
		var current_stroke = Stroke_List[strokes[i].idnum];
		for(var j=0; j<Stroke_List.length; j++) {
			if(current_stroke.id != Stroke_List[j].id) {
				current_scoring = new StrokeCompare(strokes[i], Stroke_List[j], Stroke_List[j].points);
				current_stroke.scores.push(current_scoring);
				current_scoring = new StrokeCompare(Stroke_List[j], strokes[i], strokes[i].points);
				Stroke_List[j].scores.push(current_scoring);
			}
		}
	}
}

//finds the top N strokes that are compatible
//finds all combinations of those
function findStrokeCombos(stroke, topNum){
	var prepCombo = [], fullCombos = [];
	var current_stroke = Stroke_List[stroke.idnum];
	current_stroke.scores.sort(function(a,b){return a.score-b.score});
	var topN = current_stroke.scores.slice(0, topNum);

	//prep to combo'd
	for(var j=0; j<topN.length; j++){
		prepCombo.push({idnum:topN[j].otherStroke.idnum, points:topN[j].otherPoints});
	}

	allCombos = combine(prepCombo, 1);
	//make sure the current stroke is present in all of them
	for(var k=0; k<allCombos.length; k++) {
		allCombos[k].push({idnum:stroke.idnum, points:stroke.points});
		fullCombos.push(allCombos[k]);
	}
	//this stroke alone
	//if(fullCombos.length > 1)
		fullCombos.push([{idnum:stroke.idnum, points:stroke.points}]);

	return fullCombos;
}

function multipleFindStrokeCombos(strokes, topNum){
	var output = [];
	for(var i=0; i<strokes.length; i++){
		output.push(findStrokeCombos(strokes[i], topNum));
	}
	return output;
}

//turns this: [[1, [x1,y1]],[2, [x2, y2]], [3,[x3,y3]]]
//into this: [[1,2,3], [x1,y1,x2,y2,x3,y3]]
function concatArray(combos){
	var stroketempid = [], stroketemppt = [];
	var output = [];
	for(var i=0; i<combos.length; i++){
		for(var j=0; j<combos[i].length; j++){
            for(var k=0; k<combos[i][j].length; k++){   
                //stroketemppt = stroketemppt.concat(combos[i][j][k].points.slice());
			    stroketemppt.push(combos[i][j][k].points.slice());
			    stroketempid.push(combos[i][j][k].idnum);
            }
		
			output.push({ids:stroketempid.slice(), points:stroketemppt.slice()});
			stroketempid = [];
			stroketemppt = [];
        }
	}
	return output;
}

function recognizeStrokes(combos, prevCombos){
	var dollar = new NDollarRecognizer(true);
	var results = prevCombos;
	for(var i=0; i<combos.length; i++){
		var result = dollar.Recognize(combos[i].points, true, false, true);
		results.push({name:result.Name, ids:combos[i].ids ,score:result.Score});
	}
	return results;
}

function recognizePrimitives(strokelist, scores){
	var i=0;
	var results = [], matched = [], chosenCombos = [];
	var chosen = true;
	//match every line to something
	while(matched.length != strokelist.length && i < scores.length){
		var indicies = scores[i].ids;

		for(var j=0; j<indicies.length; j++){
			var index = binarySearch(matched, indicies[j]);
			//we found something that we've already chosen
			if(index != -1){
				chosen = false;
				break;
			}
		}
		if(chosen == true){
			chosenCombos.push(scores[i]);
			results.push(new Primitive(scores[i].ids, scores[i].name, scores[i].score));
		}
		for(var k=0; k<scores[i].ids.length; k++)
			matched.push(scores[i].ids[k]);

		matched.sort(function(a, b){return a-b});
		i++;
	}
	return {results:results, prevCombos:chosenCombos};
}

//can enter in more than 1
function processStrokes(strokes, topNum) {
	var allNewCombos = [], allScores = [], objectList = [], allPrim = [], allObjs = [];

	//find scores for all new strokes against all other strokes
	scoreStrokes(strokes);

	//for each stroke sort them and find combinations of top [topNum]
	allNewCombos = multipleFindStrokeCombos(strokes, topNum);
	allNewCombos = concatArray(allNewCombos);
	printTo(allNewCombos, 'one', 1);
	//scores them all
	allScores = recognizeStrokes(allNewCombos, Prev_Combos_List);
	allScores.sort(function(a,b){return b.score-a.score;});
	printTo(allScores, 'two', 2);

	allPrim = recognizePrimitives(Stroke_List, allScores);
	Prev_Combos_List = allPrim.prevCombos;
	printTo(allPrim.results, 'three', 3);
	allPrim = refinePrimitives(allPrim.results);
	printTo(allPrim, 'four', 3);

	allObjs = primitivesToObjects(allPrim);

	return allObjs;
}	

function printTo(results, place, code){
	var output = '', arr = results;
    for (var p in arr) {
    	if(code == 1)
        	output += JSON.stringify(arr[p].ids)+'<br>';
        if(code == 2)
        	output += JSON.stringify(arr[p].ids)+'<br>'+arr[p].score+'<br>';
        if(code == 3)
        	output += arr[p].name+' '+JSON.stringify(arr[p].ids)+'<br>'+arr[p].score+'<br>';
    }
    document.getElementById(place).innerHTML =  output;
}
















//returns in radians
// function rotationAngle(corners, center){
// 	if(corners.length != 4){
// 		console.log('Incorrect # of corners');
// 		return 0;
// 	}
// 	var n1 = angle2Points(corners[0], corners[2]);
// 	var n2 = angle2Points(corners[1], corners[3]);

// 	var sum = n1 + n2;
// 	if(sum%90 < 8){
// 		//console.log('redone');
// 		sum = sum - (sum%90);
// 	}
// 	//console.log("angles ", n1, n2, sum);
// 	//return sum;

// 	return math.atan((corners[3].y-corners[0].y)/(corners[3].x-corners[0].x));
// }

// function drawSquare(primitive, color){
// 	var curr_stroke = primitive.pts,
// 		corners = primitive.corners,
//     	center = primitive.center,
//     	avg = primitive.cdistance,
//     	angle = rotationAngle(corners, center);

//     //draw square
//     var idn = drawQuad(center.x-avg, center.y-avg, avg*2, avg*2, angle, color)
//     //center point
//     drawpointmarker(center.x, center.y, "#FFFFFF");
//     //mark corners
//     for(var i=0; i<corners.length; i++){
//     	drawpointmarker(corners[i].x, corners[i].y, "#FFFF00");
//     }

//     return idn;
//     //score the square
//     //var r = reorder(squarePoints(center.x, center.y, avg, angle, 24));
//     //var score = similarityScore(r, curr_stroke.points);
// }

// function fractiondistance(p1x, p1y, p2x, p2y, i, n){
// 	return(new Point(( p1x*(1-(i/n)) +p2x*(i/n) ),(p1y*(1-(i/n)) + p2y*(i/n)) ));
// }

// //given center, and radius of square
// function squarePoints(cx, cy, rad, angle, n){
// 	if(n%4 != 0)
// 		return [];
// 	var uL, uR, bR, bL;
// 	var output = [];
// 	uL = rotatepoint(cx, cy, cx-rad, cy-rad, angle);
// 	uR = rotatepoint(cx, cy, cx+rad, cy-rad, angle);
// 	bR = rotatepoint(cx, cy, cx+rad, cy+rad, angle);
// 	bL = rotatepoint(cx, cy, cx-rad, cy+rad, angle);
// 	output.push(uL);
// 	output.push(uR);
// 	output.push(bR);
// 	output.push(bL);
// 	//number of points per side need to add
// 	var segments = ((n-4)/4) + 1;
// 	for(var i=1; i<segments; i++){
// 		//uL to uR, uR to bR, bR to bL, bL to uL
// 		output.push(fractiondistance(uL.x, uL.y, uR.x, uR.y, i, segments));
// 		output.push(fractiondistance(uR.x, uR.y, bR.x, bR.y, i, segments));
// 		output.push(fractiondistance(bR.x, bR.y, bL.x, bL.y, i, segments));
// 		output.push(fractiondistance(bL.x, bL.y, uL.x, uL.y, i, segments));
// 	}
// 	return output;
// }

//reorders all points based on angle to center
// function reorder(points){
// 	var smartpoints = [];
// 	var center = centroid(points);

// 	for(var i=0; i<points.length; i++){
// 		var p = new SmartPoint(points[i].x, points[i].y,
// 			angle2Points(points[i], center), distance(points[i], center));
// 		smartpoints.push(p);
// 	}
// 	smartpoints.sort(function(a, b){
// 		if(b.angleToCenter == a.angleToCenter)
// 			return b.distToCenter - a.angleToCenter;
// 		return b.angleToCenter - a.angleToCenter;
// 	});
// 	return smartpoints;
// }

//finds the strokes in the future to be matched and recognized with
// function findStrokes(index, strokelist, futurenum){
// 	var indicies = [];
// 	for(var i=index; i<strokelist.length; i++){
// 		if(indicies.length < futurenum){
// 			indicies.push(i);
// 		}
// 		else{
// 			return indicies;
// 		}
// 	}
// 	return indicies;
// }



//choose the combos of lines with the highest scores and make sure that
//everything is classified to something (even to nothing)
// function choosePrimitives(history, scores){
// 	var i=0;
// 	var temp = history;
// 	var results = [];
// 	var toadd = [];
// 	var matched = [];
// 	var chosen = true;
// 	//match every line to something
// 	while(matched.length != temp.length && i < scores.length){
// 		//go through all possibilities, sorted by score
// 		//read through each possibility's line dependencies
// 		var indicies = scores[i].currentindicies;

// 		var combined_strokes = combineStrokes(indicies);
// 		var corners = findCorners(combined_strokes);
// 		//var iscorrect = shapeRules(corners, scores[i].result.Name);

// 		for(var j=0; j<indicies.length; j++){
// 			//if(iscorrect == true){
// 				var index = binarySearch(matched, indicies[j]);
// 				if(index == -1){
// 					toadd.push(indicies[j]);
// 				}
// 				//oh no we need to undo what we did this loop
// 				else{
// 					toadd.splice(0, toadd.length);
// 					break;
// 				}
// 			//}
// 		}
// 		if(toadd.length != 0){
// 			//results.push(scores[i]);
// 			results.push(new Primitive(scores[i].currentindicies, scores[i].result.Name));
// 		}
// 		for(var k=0; k<toadd.length; k++)
// 			matched.push(toadd[k]);

// 		matched.sort(function(a, b){return a-b});
// 		toadd.splice(0, toadd.length);
// 		i++;
// 	}
// 	return results;
// }

// //are the corners relatively equal distance?
// //returns an average from the average of distances from corners
// function squareTest(primitive){
// 	var sum = 0, sumpercent=0;
// 	var cornerdists = cornerDistances(primitive.corners);
// 	for(var i=0; i<cornerdists.length; i++)
// 		sum += cornerdists[i];
// 	var avg = sum/cornerdists.length;
// 	for(var i=0; i<cornerdists.length; i++){
// 		var p = math.abs((cornerdists[i] - avg)/avg);
// 		sumpercent += p;
// 	}
// 	sumpercent = sumpercent/cornerdists.length;
// 	return sumpercent;
// }
