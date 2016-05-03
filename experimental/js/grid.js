var CANVAS_WIDTH = 500;
var CANVAS_HEIGHT = 500;

//div = divisions per side
function CanvasGrid(w, h, divW, divH){
	this.width = w;
	this.height = h;
	this.divW = divW;
	this.divH = divH;
	this.wLen = w/divW;
	this.hLen = h/divH;
	this.elements = setupGrid(w/divW, h/divH);
}

function findBlock(point, grid){
	if(point.x < 0 || point.x > grid.width || point.y < 0 || point.y > grid.height)
		return [-1,-1];
	return [Math.floor(point.x/grid.wLen), Math.floor(point.y/grid.hLen)];
}

function addStrokeToGrid(stroke, grid, nth){
	for(var i=0; i<stroke.length; i+=nth){
		if(i < stroke.numPoints){
			var index = findBlock(stroke.points[i], grid);
			var toPush = grid.elements[index[0]][index[1]].contents;
			//check if it's there
			var isThere = binarySearch(toPush, stroke.id);
			if(isThere == -1){
				toPush.push(stroke.id);
				toPush.sort();
			}
		}
	}
	return grid;
}

function gridElement(minX, minY, maxX, maxY){
	this.minX = minX;
	this.minY = minY;
	this.maxX = maxX;
	this.maxY = maxY;
	this.contents = [];
}

//nH/nW is the dimensions of each block
function setupGrid(nH, nW) {
	var canvasGrid = [];
	var canvasGridElement = [];
	var currH = 0, currW = 0;

	while(currW <= CANVAS_WIDTH-nW){
		while(currH <= CANVAS_HEIGHT-nH){
			canvasGridElement.push(new gridElement(currW, currH, currW+nW, currH+nH));
			currH += nH;
		}
		canvasGrid.push(canvasGridElement.splice(0));
		canvasGridElement = [];
		currH = 0;
		currW += nW;
	}
	return canvasGrid;
}

function practicegrid(div){
	var ind = 0;
	var startstr = "grid_";
	var obj = paper.getById(startstr+ind);
	if(obj != null){
		while(obj != null){
			obj.remove();
			ind++;
			obj = paper.getById(startstr+ind);
		}
	}
	else{
		var lines = [];
		for(var i=1; i<div; i++){
			lines.push("M" + 0 + " " + CANVAS_HEIGHT/div*i + "L" + CANVAS_WIDTH + " " + CANVAS_HEIGHT/div*i);
			lines.push("M" + CANVAS_WIDTH/div*i + " " + 0 + "L" + CANVAS_WIDTH/div*i + " " + CANVAS_HEIGHT);
		}
		for(var i=0; i<lines.length; i++){
			var gridLine = paper.path(lines[i]);
			gridLine.id = startstr + ind;
			ind++;
		}
	}

}

function printGrid(grid, id){
	var output = "";
	var gr = grid.elements;
	for(var i=0; i<gr[0].length; i++){
		for(var j=0; j<gr.length; j++){
			var isThere = binarySearch(gr[j][i].contents, id);
			if(isThere != -1)
				output += "1 ";
			else
				output += "0 ";
		}
		output += "\n";
	}
	return output;
}
//go through the grid and find ones close gridwise
function findClose(grid,stroke){
    var found = [];
    //go through all gridpoints
    for(var i=0; i<grid.elements.length; i++){
        for(var j=0; j<grid.elements[i].length; j++){
            var currGridEle = grid.elements[i][j];
            var isThere = binarySearch(currGridEle.contents,stroke.id);
            //find all gridelements that contain the stroke
            if(isThere != -1){
                for(var k=0; k<currGridEle.contents.length; k++){
                    var foundAlready = binarySearch(found, currGridEle.contents[k]);
                    if(foundAlready == -1){
                        if(currGridEle.contents[k] != stroke.id){
                           found.push(currGridEle.contents[k]);
                           found.sort();
                        }
                    }
                }
            }
        }
    }
    return found;
}
//just get all the strokes
function findClose2(stroke){
    var output = [];
    for(var i=0; i<Stroke_List.length; i++){
        if(Stroke_List[i].type == 'stroke' && Stroke_List[i].id != stroke.id)
            output.push(Stroke_List[i].id);
    }   
    return output;
}

function findById(strokeList, strokeId){
    for(var i=0; i<strokeList.length; i++){
        if(strokeList[i].id == strokeId){
            return strokeList[i];    
        }    
    }   
    return -1;
}

function withinPercent(a, b){
    return Math.min(Math.abs((Math.abs(a)-Math.abs(b)))/Math.abs(a), Math.abs((Math.abs(b)-Math.abs(a)))/Math.abs(b));
}

function withinDiff(a,b){
    return Math.abs(Math.abs(a)-Math.abs(b));
}

function findClosest(stroke){
    var closest, closestDist = 9999;
    for(var i=0; i<Stroke_List.length; i++){
        var dist = distance(stroke.center, Stroke_List[i].center);
        if(dist < closestDist && stroke.id != Stroke_List[i].id && Stroke_List[i].removed == false){
            closest = Stroke_List[i];
            closestDist = dist;
        }
    }
    return closest;
}

//given a list of strokes, find the ones that are close and similar
//from those, pick the best one
function findReplaceable(stroke, closeStrokes){
    var suitable = [];
    for(var i=0; i<closeStrokes.length; i++){
        var nearbyStroke = findById(Stroke_List, closeStrokes[i]);
        if(nearbyStroke != -1){
            //slopes are similar
            var slopeD = withinPercent(nearbyStroke.bestFitLine.slope, stroke.bestFitLine.slope);
            var lengP = withinPercent(nearbyStroke.length, stroke.length);
            var centerD = distance(stroke.center, nearbyStroke.center);
            //console.log(slopeD, lengP, centerD);
            if( slopeD < .5 && lengP < .4 && centerD < 20 && stroke.id != nearbyStroke.id &&
                nearbyStroke.removed == false){  
                suitable.push({stroke:nearbyStroke, id:suitable.length, score:slopeD+lengP});
            }
        }
        else{
            console.log("strokeId not found: Error ", nearbyStroke[i]);
        }
    }
    if(suitable.length > 1)
       suitable.sort(function(a, b){return a.score-b.score})
    if(suitable.length > 0)
        return [suitable[0].stroke];
    return suitable;
}

function iterativeSearch(ds, key){
    for(var i=0; i<ds.length; i++){
        if(ds[i].id == key)
            return i;
    }
    return -1;
}

function removeStroke(nearby){
    var index = iterativeSearch(Stroke_List, nearby.id);
    Stroke_List[index].removed = true;
    var id = Stroke_List[index].id;
    var obj = paper.getById(id);
    try{
        obj.remove();

    }
    catch(err){
        console.log("ERROR removing similar stroke");
        console.log(id);
        console.log(Stroke_List[index]);
        paper.forEach(function(obj){
                console.log(obj.id)
        })
    }
}

function overwrite(lastStroke){
    if(lastStroke.type == 'stroke'){
        var closeStrokes = findClose2(lastStroke);
        closeEnough = findReplaceable(lastStroke, closeStrokes);

        //delete the old line 
        if(closeEnough.length > 0)
           removeStroke(closeEnough[0]);
    }
    else if(lastStroke.type == 'scribble'){
        if(Stroke_List.length > 1){
            var closest = findClosest(lastStroke);
            if(distance(closest.center, lastStroke.center) < 25){
                removeStroke(closest);
                removeStroke(lastStroke);
            }
        }
    }
}

function findCloser(pt, choice1, choice2){
    if(distance(pt, choice1) < distance(pt, choice2))
        return choice1;
    return choice2;
}

function travelLine(slope, length, start, closeTo){
    var px = length/Math.sqrt(1+(slope*slope));
    var py = (slope*length)/Math.sqrt(1+(slope*slope));

    var p1 = new Point(start.x+px, start.y+py);
    var p2 = new Point(start.x-px, start.y-py);
    
    return findCloser(closeTo, p1, p2);
}

function findEndPoint(startPt, endPt, strokeId, length){
    var index = iterativeSearch(Stroke_List, strokeId);
    var wallStroke = Stroke_List[index];
    var slope = wallStroke.bestFitLine.slope;
    
    var wallEnd = findCloser(endPt, wallStroke.points[0], wallStroke.points[wallStroke.points.length-1]);
    var windEnd = travelLine(slope, length, startPt, endPt);
    var windLen = distance(startPt, endPt);
    
    var windSlope = (endPt.y - startPt.y)/(endPt.x - startPt.x);

    if(!isFinite(windSlope))
        windSlope = 999999;
    if(windSlope == 0)
        windSlope = .00001;

    var a1 = Math.atan(slope);
    var a2 = Math.atan(windSlope);

    var k = withinPercent(a1, a2);
    var j = withinDiff(a1, a2);
    //console.log(a1, a2, k, j);
    //window is too long
    if( k > .3 && j > .5){
        return -1;
    }
    if(distance(startPt, wallEnd) < windLen){
        var newWindEnd = travelLine(slope, wallStroke.length/20, wallEnd, startPt);
        return newWindEnd;
    }
    else{
        return windEnd;
    }
}

function findPrintedPath(startPoint, endPoint, clickedOn, windowMode, shiftDown, RESAMPLE_SIZE){
    var simplified;
    if(windowMode) {
        var calcEnd = findEndPoint(startPoint, endPoint, clickedOn, distance(startPoint, endPoint));
        if(calcEnd == -1){
            path.remove();
            windowMode = false;
            return;
        } 
        simplified = Resample([startPoint, calcEnd], RESAMPLE_SIZE);
    }
    else if(shiftDown) {
        simplified = Resample([startPoint, endPoint], RESAMPLE_SIZE);
    }
    else {
        if(isLine(lastpath, 0, lastpath.length-1)) {
            simplified = Resample([lastpath[0], lastpath[lastpath.length-1]], RESAMPLE_SIZE);
        }
        else {
            simplified = Resample(lastpath, RESAMPLE_SIZE);
            //simplified = lastpath;
        }
    }
    return simplified;
}