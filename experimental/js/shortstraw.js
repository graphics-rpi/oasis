function pathLength(pts, a, b){
	var sum = 0;
	for(var i=a; i<b; i++)
		sum += distance(pts[i], pts[i+1]);
	return sum;
}

function randomScore(pts, num){
	var index = 0, forward = num;
	var randomness = 0;

	while(forward < pts.length){
		randomness += (pathLength(pts, index, forward) - distance(pts[index], pts[forward]));
		index += num;
		forward += num;
	}
	return randomness/pathLength(pts, 0, pts.length-1);
}

function isLine(points, a, b){
	var threshold = .95;
	var dist = distance(points[a], points[b]);
	var pathDist = pathLength(points, a, b);
	if(dist/pathDist > threshold)
		return true;
	return false;
}

function halfwayCorner(straws, a, b){
	var quarter = (b-a)/4;
	var min = Number.MAX_SAFE_INTEGER;
	var minIndex = 0;
	for(var i=(a+quarter); i<(b-quarter); i++){
		if(straws[i] < min){
			min = straws[i];
			minIndex = i;
		}
	}
	return minIndex;
}

function postProcessCorners(points, corners, straws){
	var cont = false;
	while(!cont){
		cont = true;
		for(var i=1; i<corners.length; i++){
			var c1 = corners[i-1];
			var c2 = corners[i];
			if(!isLine(points, c1, c2)){
				var newCorner = halfwayCorner(straws, c1, c2);
				if(newCorner > c1 && newCorner < c2){
					corners.splice(i, 0, newCorner);
					cont = false;
				}
			}
			cont = true;
		}
	}
	for(var i=1; i<corners.length-1; i++){
		var c1 = corners[i-1];
		var c2 = corners[i+1];
		if(isLine(points, c1, c2)){
			corners.splice(i, 1);
			i--;
		}
	}
	return corners;
}

function shortStraw(points){
	var corners = [], straws = [];
	corners.push(0);
	var w = 3;
	for(var i=w; i<points.length-w; i++){
		straws.push(distance(points[i-w], points[i+w]));
	}
	var copy = straws.slice();
	var t = copy[copy.length/2]* .95;

	for(var i=w; i<points.length-w; i++){
		if(straws[i-w] < t){
			localMin = Number.MAX_SAFE_INTEGER;
			localMinIndex = i;
			while(i < straws.length && straws[i-w] < t){
				if(straws[i-w] < localMin){
					localMin = straws[i-w];
					localMinIndex = i;
				}
				i++;
			}
			corners.push(localMinIndex);
		}
	}
	corners.push(points.length-1);
	corners = postProcessCorners(points, corners, straws);
	var output = [];
	for(var i=0; i<corners.length; i++){
		output.push(points[corners[i]]);
	}

	return output;
}