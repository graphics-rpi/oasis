var Stroke_List = [];
var Prev_Combos_List = [];
var Object_List = [];
var Shape_Object_List = [];
var cornerpoints = [];
var cornercount = 0;
var objidcount = 0;
var lastprocessed = 0;
var HISTORY_DEPTH = 7;

var ObjectTemplates = [];
ObjectTemplates.push(new ObjectTemplate("wardrobe", ["rect", "W"]));
ObjectTemplates.push(new ObjectTemplate("bed", ["rect", "B"]));
ObjectTemplates.push(new ObjectTemplate("desk", ["rect", "D"]));

var FurnitureTemplates = [];
FurnitureTemplates.push(new FurnitureTemplate('bed','twin',100,200,'blue'));
FurnitureTemplates.push(new FurnitureTemplate('bed','full',138,200, 'red'));
FurnitureTemplates.push(new FurnitureTemplate('bed','queen',150,213, 'green'));
FurnitureTemplates.push(new FurnitureTemplate('bed','king',200,213, 'yellow'));

FurnitureTemplates.push(new FurnitureTemplate('desk','medium',85,175, 'purple'));

FurnitureTemplates.push(new FurnitureTemplate('wardrobe','small',150,200, 'brown'));
FurnitureTemplates.push(new FurnitureTemplate('wardrobe','large',200,300, 'pink'));

function Stroke(id, idnum, pts, resampleSize, type){
	this.id = id;
	this.idnum = idnum;
    this.type = type;
	this.length = strokeLength(pts);
	this.points = pts;
	this.numPoints = this.points.length;
	this.midpoint = {x:(this.points[0].x + this.points[this.points.length-1].x)/2,
						y:(this.points[0].y + this.points[this.points.length-1].y)/2};
	this.center = centroid(pts);
	this.corners = shortStraw(this.points);
	this.bestFitLine = leastSquares(this.points);
	this.lengthRatio = lengthRatio(this.points, this.length);
    this.removed = false;
    this.windows = [];
    this.scores = [];
}

//the scoring between any 2 strokes
function StrokeCompare(str1, str2, pts){
	this.stroke = str1;
	this.otherStroke = str2;
	this.otherPoints = pts;
	this.timeDist = Math.abs(str1.idnum - str2.idnum);
	this.eucDist = distance(str1.center, str2.center);
	this.score = strokeScoring(this.timeDist, (this.eucDist/100),
		this.stroke.bestFitLine.slope, this.otherStroke.bestFitLine.slope);
}

function strokeScoring(timeDist, eucDist, myAngle, otherAngle){
	return (eucDist*eucDist + Math.abs(myAngle-otherAngle)/20);
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

function PaperObject(id, name, points, strokes, center, corners, cdist){
	this.id = id;
	this.name = name;
	this.strokes = strokes;
	this.center = center;
	this.corners = corners;
	this.cdist = cdist;
	this.points = points;
	this.simplifiedPoints = pointSimplification(points, HISTORY_DEPTH);
}

function ShapeObject(id, type, points, strokes){
	this.id = id;
	this.type = type;
	this.strokes = strokes;
	this.center = centroid(points);
	this.points = points;
}

function ObjectTemplate(name, primitives){
	this.name = name;
	this.primitives = primitives;
}

function FurnitureTemplate(name, size, h, w, color){
	this.name = name;
	this.size = size;
	this.height = h;
	this.width = w;
	this.color = color;
	this.ratio = h/w;
}

function Rectangle(rect, score, fType, strokes){
	this.rect = rect;
	this.score = score;
	this.furnType = fType;
	this.strokes = strokes;

}

/////////////////////////////////////////////////////////////////////////////////////////////////
// Stroke Functions

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

//if p1 is a better fit than p2
function pointscore(p1, p2, x, y){
	if((p1.x*x + p1.y*y) > (p2.x*x + p2.y*y))
		return true;
	return false;
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

	// return Math.atan(ans['slope']);
	return ans;
}

function pointSimplification(points, n){
	var output = [];
	for(var i=0; i<points.length; i+=n){
		output.push(points[i]);
	}
	return output;
}

function lengthRatio(points, len){
	var eucD = distance(points[0], points[points.length-1]);
	var pathD = len;
	return eucD/pathD;
}

//given an array of strokes return an array of all points
function getAllPoints(strokes){
	var output = [];
	for(var i=0; i<strokes.length; i++){
		for(var j=0; j<strokes[i].points.length; j++){
			output.push(strokes[i].points[j]);
		}
	}
	return output;
}

//given an array of strokes return an array of all points (separated by stroke)
function getAllPointsSeparate(strokes){
	var output = [], m = [];
	for(var i=0; i<strokes.length; i++){
		for(var j=0; j<strokes[i].points.length; j++){
			m.push(strokes[i].points[j]);
		}
		output.push(m.slice(0));
		m = [];
	}
	return output;
}

function getStrokesById(strokeIds){
	var output = [];
	for(var i=0; i<strokeIds.length; i++){
		output.push(Stroke_List[strokeIds[i]]);
	}
	return output;
}

function createShapeId(type){
	var id = type + '_' + objidcount;
	objidcount++;
	return id;
}
////////////////////////////////////////////////////////////////////////////////////////////////
//Point Manuipulation

//find angle between 2 points with respect to origin
function angle2Points(p1, p2){
	return Math.atan2(p2.y - p1.y, p2.x - p1.x) * 180 / Math.PI;
}

//angle between 2 points at a fixed point
function angle2PointsFixedPoint(p1, p2, fixed){
	var a1 = angle2Points(p1, fixed);
	var a2 = angle2Points(p2, fixed)
	return Math.abs(a1-a2);
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

function angleAwayFromCenter(cx, cy, px, py){
	return (Math.atan2(py - cy, px - cx) * 180 / Math.PI)+90;
}

////////////////////////////////////////////////////////////////////////////////////////////////
//Searching for information

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

////////////////////////////////////////////////////////////////////////////////////////////
//Tests for Primitives

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

function createObjectId(objName){
	var oId = objName + '_' + objidcount;
    objidcount++;
    return oId;
}

////////////////////////////////////////////////////////////////////////////////////////////
//Drawing Functions

function drawQuad(x,y,h,w,angle,color,id){
	var rect = paper.rect(x, y, h, w);
    rect.rotate(angle);
    rect.attr({fill:color, "opacity": .5});
    rect.toBack();
    rect.id = id;
}

function drawMarker(x,y,color){
	var rect = paper.circle(x, y, 100);
    rect.attr({fill:color, "opacity": .5});
    rect.toBack();
}

function drawRectangle(object, color){
	var corners = object.corners;
	var center = object.center;
	var id = object.id;

	if(corners.length != 4)
		return "ERROR";

    var d = bestFitRect(object);
    // var d = bestFixedSizeRect(object, 'wardrobe');
   	console.log(d);

    drawQuad(d.rect.cx-(d.rect.w/2), d.rect.cy-(d.rect.h/2), d.rect.w, d.rect.h, (360-d.rect.angle), color, id);
}

function drawRectSimple(rect, color){
	var id = 'test';
	drawQuad(rect.cx-(rect.w/2), rect.cy-(rect.h/2), rect.w, rect.h, (360-rect.angle), color, id);
}

function rectangleFitter(strokes){
	var str = getStrokesById(strokes.strokes);
	var r = bestFitRectStrokes(str);
	return r;
}

function drawRectangleStrokes(rectangle, color){
	var r = rectangle;
	var id = createShapeId('rect');
	Object_List.push(id);
	drawQuad(r.rect.cx-(r.rect.w/2), r.rect.cy-(r.rect.h/2), r.rect.w, r.rect.h, (360-r.rect.angle), color, id);
}

////////////////////////////////////////////////////////////////////////////////////////////
//Recursive Scoring

//rectangle will always be axis aligned - rotate the point (it's easier)
function rectDistance(centerx, centery, width, height, px, py) {
    var dx = Math.max(Math.abs(px - centerx) - width / 2, 0);
	var dy = Math.max(Math.abs(py - centery) - height / 2, 0);

	//it's inside
	if(dx == 0 && dy == 0){
		return Math.min(Math.abs(py-centery-(height/2)),
						Math.abs(py-centery+(height/2)),
						Math.abs(px-centerx-(width/2)),
						Math.abs(px-centerx+(width/2)));
	}
    return Math.sqrt(dx*dx + dy*dy);
}

//rect will be rect:{cx, cy, w, h}
//test: {cx:50, cy:50, w:100, h:50}
function rectScore(points, rect){
    var sum = 0;
    var rotated = rotateSet(points, {x:rect.cx, y:rect.cy}, rect.angle);
    for(var i=0; i<rotated.length; i++){
        sum += rectDistance(rect.cx, rect.cy, rect.w, rect.h, rotated[i].x, rotated[i].y);
    }
    return sum;
}

function rotatePt(pX, pY, cX, cY, angle) {
	angle = angle * Math.PI/180.0;
	return {
		x: (Math.cos(angle) * (pX-cX)) - (Math.sin(angle) * (pY-cY)) + cX,
		y: (Math.sin(angle) * (pX-cX)) + (Math.cos(angle) * (pY-cY)) + cY
	};
}

function rotateSet(points, center, angle){
	var p = [];
	for(var i=0; i<points.length; i++){
		p.push(rotatePt(points[i].x, points[i].y, center.x, center.y, angle));
	}
	return p;
}

function recursiveScoring(points, rect, prevScore, inc){
	var increment = 20, inc2 = 30;
	//score the current rect
	var output = [];
	var currScore = rectScore(points, rect);
	output.push({score: currScore, rect: rect});
	console.log(rect.angle, rect.h, rect.w, currScore, inc);
	//if its not better than prev, then stop
	if(inc > 10){
		if(currScore > prevScore)
			return {score: prevScore, rect: rect};
		else
			return {score: currScore, rect: rect};
	}

	if(currScore > prevScore && currScore < prevScore )
		return {score: prevScore, rect: rect};
	else{
		//otherwise keep going
		if(rect.angle < 180){
			var rect1 = {cx:rect.cx, cy:rect.cy, w:rect.w, h:rect.h, angle:rect.angle+increment};
			var pts1 = rotateSet(points, {x:rect.cx,y:rect.cy}, increment);
			var out1 = recursiveScoring(pts1, rect1, currScore, inc+1);
			output.push(out1);
		}
		if(rect.w < 300){
			var rect3 = {cx:rect.cx, cy:rect.cy, w:rect.w+inc2, h:rect.h, angle:rect.angle};
			var pts3 = points;
			var out3 = recursiveScoring(pts3, rect3, currScore, inc+1);
			output.push(out3);
		}
		if(rect.h < 300){
			var rect4 = {cx:rect.cx, cy:rect.cy, w:rect.w, h:rect.h+inc2, angle:rect.angle};
			var pts4 = points;
			var out4 = recursiveScoring(pts4, rect4, currScore, inc+1);
			output.push(out4);
		}

		output.sort(function(a,b){return a.score-b.score});

		return output[0];
	}
}

function incrementCalc(inc, type){
	if(inc == 0)
		inc = 1;
	var angleStart = 20, whStart = 30, xyStart = 20;
	if(type == "angle"){
		return Math.ceil(angleStart/inc);
	}
	else if(type == "wh"){
		return Math.ceil(whStart/inc);
	}
	else if(type == "xy"){
		return Math.ceil(xyStart/inc);
	}
	else{
		console.log("ERROR IncrementCalc: Type unknown")
	}
}

function iterativeScoring(points, rect, prevScore, inc){
	var aInc = incrementCalc(inc,'angle'), whInc = incrementCalc(inc,'wh'), xyInc = incrementCalc(inc,'xy');
	//score the current rect
	var output = [], results = [];
	var rects = [{cx:rect.cx, cy:rect.cy, w:rect.w, h:rect.h, angle:rect.angle+aInc},
		{cx:rect.cx, cy:rect.cy, w:rect.w, h:rect.h, angle:rect.angle-aInc},
		{cx:rect.cx, cy:rect.cy, w:rect.w+whInc, h:rect.h, angle:rect.angle},
		{cx:rect.cx, cy:rect.cy, w:rect.w, h:rect.h+whInc, angle:rect.angle},
		{cx:rect.cx, cy:rect.cy, w:rect.w-whInc, h:rect.h, angle:rect.angle},
		{cx:rect.cx, cy:rect.cy, w:rect.w, h:rect.h-whInc, angle:rect.angle},
		{cx:rect.cx+xyInc, cy:rect.cy, w:rect.w, h:rect.h, angle:rect.angle},
		{cx:rect.cx, cy:rect.cy+xyInc, w:rect.w, h:rect.h, angle:rect.angle},
		{cx:rect.cx-xyInc, cy:rect.cy, w:rect.w, h:rect.h, angle:rect.angle},
		{cx:rect.cx, cy:rect.cy-xyInc, w:rect.w, h:rect.h, angle:rect.angle}];

	var actions = ['+angle','-angle','+width','+height','-width',
		'-height','+xaxis','+yaxis','-xaxis','-yaxis'];
	for(var i=0; i<rects.length; i++){
		results.push({rect:rects[i], score:rectScore(points, rects[i]), action:actions[i]});
	}
	results.sort(function(a,b){return a.score-b.score});

	//there is a better one (pick the best of all options)
	if(inc > 750){
		console.log('max stack size');
		return {rect:rect, score:prevScore};
	}
	if(results[0].score < prevScore){
		return iterativeScoring(points, results[0].rect, results[0].score, inc+1);
	}
	else{
		return {rect:rect, score:prevScore};
	}
}

function iterativeScoringFixedSize(points, rect, prevScore, inc){
	var aInc = incrementCalc(inc,'angle'), whInc = incrementCalc(inc,'wh'), xyInc = incrementCalc(inc,'xy');
	//score the current rect
	var output = [], results = [];
	var rects = [{cx:rect.cx, cy:rect.cy, w:rect.w, h:rect.h, angle:rect.angle+aInc},
		{cx:rect.cx, cy:rect.cy, w:rect.w, h:rect.h, angle:rect.angle-aInc},
		{cx:rect.cx+xyInc, cy:rect.cy, w:rect.w, h:rect.h, angle:rect.angle},
		{cx:rect.cx, cy:rect.cy+xyInc, w:rect.w, h:rect.h, angle:rect.angle},
		{cx:rect.cx-xyInc, cy:rect.cy, w:rect.w, h:rect.h, angle:rect.angle},
		{cx:rect.cx, cy:rect.cy-xyInc, w:rect.w, h:rect.h, angle:rect.angle}];

	var actions = ['+angle','-angle','+xaxis','+yaxis','-xaxis','-yaxis'];
	for(var i=0; i<rects.length; i++){
		results.push({rect:rects[i], score:rectScore(points, rects[i]), action:actions[i]});
	}
	results.sort(function(a,b){return a.score-b.score});

	//there is a better one (pick the best of all options)
	if(inc > 500){
		console.log('max stack size');
		return {rect:rect, score:prevScore};
	}
	if(results[0].score < prevScore){
		return iterativeScoring(points, results[0].rect, results[0].score, inc+1);
	}
	else{
		return {rect:rect, score:prevScore};
	}
}

function bestFitRect(object){
    var firstTry = {cx:object.center.x, cy:object.center.y, w:10, h:10, angle:0};
    var pts = object.simplifiedPoints;
	var out1 = iterativeScoring(pts, firstTry, 999999, 0);
	return out1;
}

function bestFitRectStrokes(strokes){
	var pts = getAllPoints(strokes);
	var cent = centroid(pts);
    var firstTry = {cx:cent.x, cy:cent.y, w:10, h:10, angle:0};
	var out1 = iterativeScoring(pts, firstTry, 999999, 0);
	return out1;
}

function bestFixedSizeRect(object, furnitureName){
	var furns = [], sizeScores = [], bestScore = 9999999, bestIndex = 0;
	for(var i=0; i<FurnitureTemplates.length; i++){
		var ft = FurnitureTemplates[i];
		if(ft.name == furnitureName){
			furns.push({cx:object.center.x, cy:object.center.y, w:ft.width, h:ft.height, angle:0})
		}
	}

    var pts = object.simplifiedPoints.slice(0, object.simplifiedPoints.length);
    for(var i=0; i<furns.length; i++){
    	sizeScores.push(iterativeScoringFixedSize(pts, furns[i], 999999, 0));
    }
    for(var i=0; i<sizeScores.length; i++){
    	if(sizeScores.score < bestScore){
    		bestIndex = i;
    		bestScore = sizeScores.score;
    	}
    }
	return sizeScores[bestIndex];
}

function testRecursiveScoring(strokes){
	var p = combineStrokes(strokes);
	var ce = centroid(p);
	var r = {cx:ce.x, cy:ce.y, w:10, h:10, angle:0};
	var f = iterativeScoring(p, r, 999999, 0);
	drawRectSimple(f.rect, '#FF0000');
	return f;
} 

////////////////////////////////////////////////////////////////////////////////////////////
//Main Functionality

//scores each stroke against all other strokes
function scoreStrokes(strokes){
	for(var i=0; i<strokes.length; i++){
		var current_stroke = Stroke_List[strokes[i].idnum];
		current_stroke.scores = [];
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

function combineArrays(arr){
	var out = [];
	for(var i=0; i<arr.length; i++){
		for(var j=0; j<arr[i].length; j++){
			out.push(arr[i][j]);
		}
	}
	return out;
}


//check to make sure they are all 'lines'
//aka the length of the stroke is close to start/end distance
function lineLengthCheck(strokes){
	for(var i=0; i<strokes.length; i++){
		if(strokes[i].lengthRatio < .9){
			return false;
		}
	}
	return true;
}

//checks that all the line endings are close to at least 1 other end point
function lineEndingsCheck(strokes){
	var endings = [], pairs = [], found = false;
	var maxCornerDist = 0;
	for(var x=0; x<strokes.length; x++){
		maxCornerDist += strokes[x].length;
	}
	maxCornerDist = maxCornerDist*.1;

	for(var i=0; i<strokes.length; i++){
		var s = Stroke_List[strokes[i]];
		endings.push({point: strokes[i].points[0], id: strokes[i]});
		endings.push({point: strokes[i].points[strokes[i].points.length-1], id: strokes[i]});
	}
	for(var i=0; i<endings.length-1; i++){
		for(var j=i+1; j<endings.length; j++){
			var b = distance(endings[i].point, endings[j].point);
			if(b < maxCornerDist && endings[i].id != endings[j].id){
				pairs.push({p1:endings[i], p2:endings[j], dist: b});
				endings.splice(j,1);
				found = true;
			}
			if(found == true){
				found = false;
				break;
			}
		}
	}
	if(pairs.length != strokes.length){
		return [];
	}
	return pairs;
}

//check that the angles are close to right angles
function lineRightAngleCheck(strokes){
	var s = strokes.slice(0);
	var center = centroid(strokes);
	var output = [];
	s.sort(function(a,b){return angleAwayFromCenter(center.x, center.y, b.midpoint.x, b.midpoint.y)
		- angleAwayFromCenter(center.x, center.y, a.midpoint.x, a.midpoint.y);});
	for(var i=0; i<s.length; i++){
		var s1 = s[i].bestFitLine.slope;
		var s2 = s[(i+1)%s.length].bestFitLine.slope;
		output.push(Math.abs(Math.atan(Math.abs(s1-s2)/(1+(s1*s2)))* 180 / Math.PI)%90);
	}
	var sum = sumArray(output);

	return Math.abs(90-sum/strokes.length);
}

function k_combinations(set, k) {
	var i, j, combs, head, tailcombs;	
	if (k > set.length || k <= 0) {
		return [];
	}
	if (k == set.length) {
		return [set];
	}
	if (k == 1) {
		combs = [];
		for (i = 0; i < set.length; i++) {
			combs.push([set[i]]);
		}
		return combs;
	}
	combs = [];
	for (i = 0; i < set.length - k + 1; i++) {
		// head is a list that includes only our current element.
		head = set.slice(i, i + 1);
		// We take smaller combinations from the subsequent elements
		tailcombs = k_combinations(set.slice(i + 1), k - 1);
		// For each (k-1)-combination we join it with the current
		// and store it to the set of k-combinations.
		for (j = 0; j < tailcombs.length; j++) {
			combs.push(head.concat(tailcombs[j]));
		}
	}
	return combs;
}

function noWindows(strokes){
	for(var i=0; i<strokes.length; i++){
		if(strokes[i].windows.length != 0)
			return false;
		if(strokes[i].type == 'window')
			return false;
	}
	return true;
}

function rectangleScore(strokes){
	var output = [];
	var kCombs = k_combinations(strokes, 4);
	var maxAngleDiff = 15;
	var maxCornerDist = 0;
	

	for(var i=0; i<kCombs.length; i++){
		for(var x=0; x<kCombs[i].length; x++){
			maxCornerDist += kCombs[i][x].length;
		}
		maxCornerDist = maxCornerDist*.2;
		//all lines are close to lines
		var a = lineLengthCheck(kCombs[i]);
		//all line endings are close to another line ending
		var b = lineEndingsCheck(kCombs[i]);
		var bsum = 0;
		for(var j=0; j<b.length; j++)
			bsum += b[j].dist;
		//corners are 90 degree angles
		//average the line endings together - is corner
		var c = lineRightAngleCheck(kCombs[i]);
		var d = noWindows(kCombs[i]);
		//corner distances are close to equal
		if(a == true && b.length == 4 && c < maxAngleDiff && d == true){
			var k = [];
			for(var j=0; j<kCombs[i].length; j++){
				k.push(kCombs[i][j].idnum);
			}
			output.push({strokes:k, score: c/maxAngleDiff + bsum/maxCornerDist});
			k = [];
		}
	}
	return output;
}

//returns the furnituretemplate whose ratio matches the best
function rectangleClassification(rectangle){
	var r = [];
	var rectRatio1 = rectangle.rect.h/rectangle.rect.w;
	var rectRatio2 = rectangle.rect.w/rectangle.rect.h;
	for(var i=0; i<FurnitureTemplates.length; i++){
		r.push({ratio:withinPercent(rectRatio1,FurnitureTemplates[i].ratio), template:i});
		r.push({ratio:withinPercent(rectRatio2,FurnitureTemplates[i].ratio), template:i});
	}
	r.sort(function(a, b){return a.ratio-b.ratio});
	return FurnitureTemplates[r[0].template];
}

function chooseBestRectangles(scores){
	var best = [], chosen = [], pchosen = [];
	var chosenrect = true;
	var s = scores, i=0;
	s.sort(function(a,b){return a.score-b.score});

	while(chosen.length < Stroke_List.length && i < s.length){
		for(var j=0; j<s[i].strokes.length; j++){
			var x = binarySearch(chosen, scores[i].strokes[j]);
			if(x == -1){
				chosen.push(s[i].strokes[j]);
				chosen.sort(function(a,b){return a-b});
			}
			else{
				chosenrect = false;
				break;
			}
		}
		if(chosenrect == false){
			chosen = pchosen.slice(0);
		}
		else{
			best.push(s[i]);
			pchosen = chosen.slice(0);
		}
		i++;
		chosenrect = true;
	}
	return best;
}


////////////////////////////////////////////////////////////////////////////////////////////
//Object placement

//returns array elements that are present in BEFORE but not AFTER
//DO NOT RETURN THE ONES TAHT ARE THE SAME
function arrayDifference(before, after) {
	var result = [];
	for (var i = 0; i < before.length; i++) {
		if (after.indexOf(before[i]) === -1) {
		  result.push(before[i]);
		}
	}
	return result;
}

function arraysEqual(a, b) {
	if (a === b)
		return true;
	if (a == null || b == null)
		return false;
	if (a.length != b.length)
		return false;

	// If you don't care about the order of the elements inside
	// the array, you should sort both arrays here.

	for (var i = 0; i < a.length; ++i) {
		if (a[i] !== b[i])
			return false;
	}
	return true;
}

//returns true is b is in array A (same points means same obj)
function aContainsB(arr, b){
	for(var i=0; i<arr.length; i++){
		if(arraysEqual(arr[i].points, b.points))
			return true;
	}
	return false;
}

function arrayDifferenceNoDups(before, after) {
	var result = [];
	for (var i = 0; i < before.length; i++) {
		if (!aContainsB(after, before[i])) {
			result.push(before[i]);
		}
	}
	return result;
}

function a(objs1, objs2){
	for(var i=0; i<objs1.length; i++){
		for(var j=0; j<objs2.length; j++){
			if(objs1.points == objs2.points){
				objs1.splice(i, 1);
				objs2.splice(j, 1);
			}
		}
	}
}

function deleteOldObjects(oldObjects, newObjects){
	var toDelete = arrayDifferenceNoDups(oldObjects, newObjects);
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
	var toAdd = arrayDifferenceNoDups(newObjects, oldObjects);
	for(var i=0; i<toAdd.length; i++){
		var obj = toAdd[i];
		if(obj.name == "wardrobe"){
			drawRectangle(obj, "#FF00FF");
		}
		else if(obj.name == "bed"){
			drawRectangle(obj, "#FFFF00");
		}
		else if(obj.name == "desk"){
			drawRectangle(obj, "#00FFFF");
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////////////

function objectCleanUp(oldObjs, newObjs){
	deleteOldObjects(oldObjs, newObjs);
    placeNewObjects(oldObjs, newObjs);
    oldObjs = newObjs.slice(0);
    return oldObjs;
}

////////////////////////////////////////////////////////////////////////////////////////////
//printing

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

function testScore(strokeIds){
	var dollar = new NDollarRecognizer(true);
	var k = [];
	for(var i=0; i<strokeIds.length; i++){
		k.push(Stroke_List[strokeIds[i]].points);
	}
	var res = dollar.Recognize(k, true, false, true);
	return {name:res.Name, score:res.Score};
}

////////////////////////////////////////////////////////////////////////////////////////////
//showing and responsive page stuff
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

function outputStrokes(){
	var output = [];
	for(var i=0; i<Stroke_List.length; i++){
		output.push(Stroke_List[i].points);
	}
	return JSON.stringify(output);
}

function rectangleCorners(rect){
	var t = [-1,-1, 1,-1, 1,1, -1,1];
	var output = [];
	for(var i=0; i<4; i++){
		var nx = rect.cx+((rect.w/2)*t[i*2]);
		nx = parseFloat(nx.toFixed(4));
		var ny = rect.cy+((rect.h/2)*t[(i*2)+1]);
		ny = parseFloat(ny.toFixed(4));
		var z = rotatePt(nx,ny,rect.cx, rect.cy, rect.angle*-1);
		output.push(z);
	}
	return output;
}

function exportStrokes(id, name, owner, rects, north, scale){
    var output = [],r = rects.slice(0);
    var str = [], pts = [], found = [];
    for(var i=0; i<r.length; i++){
        for(var j=0; j<r[i].strokes.length; j++){
            str.push({id:r[i].strokes[j], rect:i});
        }
    }
    str.sort(function(a,b){return a.id-b.id});
    for(var i=0; i<Stroke_List.length; i++){
        var x = binarySearchPaperId(str, i);
        var curr = Stroke_List[i];
        //this is a normal stroke
        if(curr.type == 'window'){}
        else if(x == -1){
            if(withinPercent(curr.length, distance(curr.points[0], curr.points[curr.points.length-1])) < .001)
                pts = [curr.points[0], curr.points[curr.points.length-1]];
            else{
                pts = curr.points;
            }
            var w = [];
            for(var j=0; j<curr.windows.length; j++){
                var curr_win = Stroke_List[curr.windows[j]];
                w.push([{x:curr_win.points[0].x, y:curr_win.points[0].y},
                    {x:curr_win.points[curr_win.points.length-1].x,
                        y:curr_win.points[curr_win.points.length-1].y,}])
            }
            output.push({type:curr.type, points:pts, windows:w});
        }

        else{
            //find if we've already added this rectangle
            var q = binarySearch(found, str[x].rect);
            if(q == -1){
                var rectangle = r[str[x].rect];
                var rx = rectangle.rect.cx-(rectangle.rect.w/2);
                rx = parseFloat(rx.toFixed(4));
                var ry = rectangle.rect.cy-(rectangle.rect.h/2);
                ry = parseFloat(ry.toFixed(4));
                var a = (rectangle.rect.angle)*(Math.PI/180);
                a = parseFloat(a.toFixed(4));

                var cn = rectangleCorners(rectangle.rect);
                var st = getAllPointsSeparate(getStrokesById(rectangle.strokes));

                output.push({type:rectangle.furnType.name, x:rx, y:ry, height:rectangle.rect.h,
                    width:rectangle.rect.w, angle:a, color:rectangle.furnType.color ,corners:cn , strokes:st});

                found.push(str[x].rect);
            }
        }
    }
    var n = ((north+180)%360)*(Math.PI/180);
    n = parseFloat(n.toFixed(4));

    var s = {model_id:id, model_name:name, owner:owner, north:n, scale:scale, items:output};
    return JSON.stringify(s);
}

function deleteAllObjects(paper){
	for(var i=0; i<Object_List.length; i++){
		var o = paper.getById(Object_List[i]);
		o.remove();
	}
	Object_List = [];
}
