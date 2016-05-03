var CANVAS_WIDTH = 600;
var CANVAS_HEIGHT = 600;

function sketchPad(canvas){
	this.canvas = canvas;
	this.strokeList = [];
	this.primitiveList = [];
	this.objectList = [];
}

function pointsToStroke(points){

}

function drawStroke(stroke){
	
}

//points should be an array!
function addStroke(sketchpad, strokes){
	for(var i=0; i<strokes.length; i++){
		sketchpad.strokeList.push(strokes[i]);
	}
	
}

function removeStroke(sketchpad, id){

}

