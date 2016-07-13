function Stroke(id, points, type){
	this.id = id;
    this.type = type;
	this.length = strokeLength(points);
	this.points = points;
	this.numPoints = this.points.length;
	this.center = centroid(pts);
	this.bestFitLine = leastSquares(this.points);
    this.removed = false;
    //this.windows = [];
}

//length of individual paths added up together
function strokeLength(points){
	var sum = 0;
	for(var i=0; i<points.length-1; i++)
		sum += distance(pts[i], pts[i+1]);
	return sum;
}

//averaged x's and y's together
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