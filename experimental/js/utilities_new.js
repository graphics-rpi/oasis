
function pointsToPath(points){
	var linepath = "";
	for(var i=0; i<points.length; i++){
    	if(i==0)
    		linepath = linepath + "M"+points[i].x+","+points[i].y+" ";
    	else
    		linepath = linepath + "L"+points[i].x+","+points[i].y+ " ";
    }
    return linepath;
}

function distance(p1, p2){
	return math.sqrt(math.pow((p2.x-p1.x),2) + math.pow((p2.y-p1.y),2));
};

function angleRadians(p1, p2){
	return Math.atan2(p2.y - p1.y, p2.x - p1.x);
}

function angleDegrees(p1, p2){
	return Math.atan2(p2.y - p1.y, p2.x - p1.x) * 180 / Math.PI;
}