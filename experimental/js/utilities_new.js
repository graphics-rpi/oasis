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
    return Math.sqrt(Math.pow((p2.x-p1.x),2) + Math.pow((p2.y-p1.y),2));
};

function angleRadians(p1, p2){
    return Math.atan2(p2.y - p1.y, p2.x - p1.x);
}

function angleDegrees(p1, p2){
    return Math.atan2(p2.y - p1.y, p2.x - p1.x) * 180 / Math.PI;
}

function strokeLength(pts){
    var sum = 0;
    for(var i=0; i<pts.length-1; i++)
        sum += distance(pts[i], pts[i+1]);
    return sum;
}

function sumArray(a){
    var sum = 0;
    for(var i=0; i<a.length; i++){
        sum = sum + a[i];
    }
    return sum;
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

function calcResize(length, resampleSize, lengPerSegement){
    return Math.round(length/lengPerSegement)*resampleSize;
}

function withinPercent(a, b){
    return Math.min(Math.abs((Math.abs(a)-Math.abs(b)))/Math.abs(a), Math.abs((Math.abs(b)-Math.abs(a)))/Math.abs(b));
}