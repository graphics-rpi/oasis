var canvas = document.getElementById('canvas');
var paper = new Raphael(canvas, CANVAS_WIDTH, CANVAS_HEIGHT);

var mousedown = false;
var lastX, lastY, path, pathString;

var lineidcount = 0;
var lastpath = [];
var RESAMPLE_SIZE = 24, RESAMPLE_LEN_PER_SEGMENT = 250;
var lineLength = 0, lineMin=25;

var laststrokes = [];
var ndollar_results = [];
var primitives = [];
var oldObjs = [], newObjs = [];
var startPoint, endPoint;
var shiftDown = false, windowMode = false;
var clickedOn;
var northArrowClick = false;
var d = new Date();
var closeEnough = [];

var gridDivisions = 20;
//var Grid = new CanvasGrid(canvasWidth, canvasHeight, gridDivisions, gridDivisions);
//practicegrid(gridDivisions);

//starting 
var northAngle = 0, northX = 235, northY = 5, northWidth = 30, northHeight = 40;

var piece = paper.image("../images/northarrow.png", 0, 0, northWidth, northHeight)
    .attr({cursor: "move", transform: "R" + northAngle + "T" + northX + "," + northY });
piece.drag(dragMove, dragStart, dragStop);

// Set up the object for dragging
function dragStart(){
    this.ox = northX;
    this.oy = northY;
    northArrowClick = true;
}
//what does the arrow do when we let go
function dragStop() {
    northAngle = angleAwayFromCenter(CANVAS_WIDTH/2,CANVAS_WIDTH/2, northX+15, northY+20);
    piece.animate({transform: "R" + northAngle + "T" + northX + "," + northY}, 500, "<>");
    northArrowClick = false;
}
//follow the mouse
function dragMove(dx, dy) {
    northX = Math.min(CANVAS_WIDTH-30, this.ox+dx);
    northY = Math.min(CANVAS_HEIGHT-40, this.oy+dy);
    northX = Math.max(0, northX);
    northY = Math.max(0, northY);            
    piece.attr({ transform: "R" + northAngle + "T" + northX + "," + northY });
}


function get_strokes(){
    var output = "<div id='listofstrokes'>";
    for(var i=0; i<Stroke_List.length; i++){
        if(Stroke_List[i].removed == false){
            output = output + "<div id=" + Stroke_List[i].id +" class= 'sItem' " +
            "onmouseover='strokeMouseOver(this.id, Stroke_List["+ i +"].type)'" +
            "onmouseout='strokeMouseOut(this.id, Stroke_List[" + i + "].type)'>"
            + Stroke_List[i].id + "</div>";
        }
    }
    output += "</div>"
    document.getElementById('strokeOutput').innerHTML = output;
}


function get_objects(){
    var output = "<div id='listofobjects'>";
    for(var i=0; i<Object_List.length; i++){
        output = output + "<div id=" + Object_List[i].id +" class= 'sItem' " +
        "onmouseover='objMouse(this.id, 'over')' onmouseout='objMouse(this.id, 'out')'>"
        + Object_List[i].id + "</div>";
    }
    output += "</div>";
    document.getElementById('objectOutput').innerHTML = output;
}

//sidebar for strokes
function strokeMouseOver(idn, type) {
    var obj = paper.getById(idn);
    if(type == 'stroke' || type == 'scribble')
        obj.attr({"stroke":"#FF0000", "stroke-width":5});
    else
        obj.attr({"stroke":"#0000FF", "stroke-width":7});
}
function strokeMouseOut(idn, type) {
    var obj = paper.getById(idn);
    if(type == 'stroke' || type == 'scribble')
        obj.attr({"stroke":"#000000", "stroke-width":3});
    else
        obj.attr({"stroke":"#0EBFE9", "stroke-width":5});
    
}

//sidebar for objects
function objMouseOver(idn) {
    var obj = paper.getById(idn);
    var strokes = getStrokesFromObject(idn);
    obj.attr({"stroke":"#FF0000", "stroke-width":5});
    var str;
    for(var i=0; i<strokes.length; i++){
        try{
            str = paper.getById(strokes[i]);
            str.attr({"stroke":"#00FFFF", "stroke-width":2});
        }
        catch(err){
            console.log("stroke not found", err);
        }
    }
}
function objMouseOut(idn) {
    var obj = paper.getById(idn);
    var strokes = getStrokesFromObject(idn);
    obj.attr({"stroke":"#000000", "stroke-width":3});
    var str;
    for(var i=0; i<strokes.length; i++){
        try{
            str = paper.getById(strokes[i]);
            str.attr({"stroke":"#000000", "stroke-width":3});
        }
        catch(err){
            console.log("stroke not found", err);
        }
    }
}

//given a strokeid, return ids of the stroks that its made of
function getStrokesFromObject(id){
    var obj = findById(Object_List, id);
    var strokes = [];
    for(var i=0; i<obj.strokes.length; i++){
        for(var j=0; j<obj.strokes[i].length; j++){
            strokes.push(obj.strokes[i][j]);
        }
    }
    for(var i=0; i<strokes.length; i++){
        strokes[i] = Stroke_List[strokes[i]].id;
    }
    return strokes;
}

//for linehovers
function hovering(e){
    this.attr({stroke: '#FF0000'});
}
function hoverout(e){
    this.animate({stroke: '#000000'});
}
//for windows draw over lines
function pathMouseDown(e){
    windowMode = true;
    clickedOn = $(this).attr("id");
    console.log("entering window MOde");
}
function pathMouseUp(e){
    windowMode = false;
    console.log("exiting window mode");
}

$(canvas).mousedown(function (e) {
    if(!northArrowClick){
        lastpath = [];
        mousedown = true;

        var x = e.offsetX, y = e.offsetY;
        lineLength = 0;
        
        startPoint = new Point(x,y);
        lastpath.push(new Point(x, y));

        pathString = 'M' + x + ' ' + y + 'l0 0';
        path = paper.path(pathString);

        lastX = x;
        lastY = y;
    }
});

$(canvas).mouseup(function () {
    if(northArrowClick == true)
        return;
    mousedown = false;
    path.remove();

    //line has a minimum length
    if(lineLength < lineMin){
        lastpath = [];
        windowMode = false;
        return;
    }

    //turns path into a processable path based on windowmode, etc.
    var processed = findPrintedPath(startPoint, endPoint, clickedOn,
        windowMode, shiftDown, RESAMPLE_SIZE);
    //find type of line, draw it, save it
    process_line(processed);
    var lastStroke = Stroke_List[Stroke_List.length-1];
    
    newObjs = processStroke(lastStroke, paper);
    oldObjs = objectCleanUp(oldObjs, newObjs);

    get_strokes();
    get_objects();

    lastpath = [];
    windowMode = false;
    //console.log("Mouse up complete");
});

$(canvas).mousemove(function (e) {
    if (!mousedown) {
        return;
    }
    if(northArrowClick){
        return;
    }
    var x = e.offsetX, y = e.offsetY;
    
    lastpath.push(new Point(x,y));
    endPoint = new Point(x,y);
    lineLength += distance(new Point(lastX, lastY), new Point(x,y));
    
    //straight line mode
    // if(shiftDown||windowMode){
    //     var newPathString = pointsToPath([startPoint, new Point(x, y)]);
    //     path.attr('path', newPathString);
    //     if(windowMode){
    //         path.attr({"stroke": "#0EBFE9", "stroke-width": 5});
    //     }
    //     endPoint = new Point(e.offsetX, e.offsetY);
    // }
    // else {
        pathString += 'l' + (x - lastX) + ' ' + (y - lastY);
        path.attr('path', pathString);
    // }
    lastX = x;
    lastY = y;
});

$(canvas).mouseleave(function () {
    if(lastpath.length > 0 && path != null){
        path.remove();
        lastpath = [];
    }
});

$(document).keydown(function(e) {
    if(e.keyCode == 16){
        if(startPoint != null){
            shiftDown = true;
            var newPathString = pointsToPath([startPoint, endPoint]);
            path.attr('path', newPathString);
            endPoint = new Point(e.offsetX, e.offsetY);
        }
    }
});

$(document).keyup(function(e) {
    //z
    if (e.keyCode == 90 && e.ctrlKey){
        if(Stroke_List.length > 0){
            var obj = paper.getById(Stroke_List[Stroke_List.length-1].id);
            Stroke_List.pop();
            obj.remove();
        }
    }
    //m
    if (e.keyCode == 77 && e.ctrlKey){
        console.log(printGrid(Grid, Stroke_List[Stroke_List.length-1].id));
    }
    //g
    if (e.keyCode == 71 && e.ctrlKey){
        practicegrid(gridDivisions);
    }
    //shift
    if(e.keyCode == 16){
        shiftDown = false;
    }
    //1
    if(e.keyCode == 49 ){
        // alert('hello');
        showCorners(Stroke_List);
    }
    //2
    if(e.keyCode == 50){
        showCorners(primitives);
    }
    //3
    if(e.keyCode == 51){
        showCorners2(primitives);
    }
});



$('#newtemplate').click(function (e) {
    $.ajax({
        type : 'POST',
        url  : '../php/add_template.php',
        data : {'name':document.getElementById('strokename').value, 'stroke':JSON.stringify(reorder(resample(simplify(lastpath, 1.5), 24)))},
        success :  function(data) {
            $("#strokelist").load("../php/read_templates.php");
            document.getElementById('spoints').innerHTML = data;
        }
    });
});

$('#clear').click(function (e) {
    for(var i=Stroke_List.length-1; i>=0; i--){
        var obj = paper.getById(Stroke_List[i].id);
        Stroke_List.pop();
        obj.remove();
    }
    console.log("history size is ", Stroke_List.length);
});

$('#saveoutput').click(function (e) {
    document.getElementById('textplace').value = outputStrokes();
});

$('#testinput').click(function (e) {
    var strokes = document.getElementById('textplace').value;
    strokes = JSON.parse(strokes);
    for(var i=0; i<strokes.length; i++){
        process_line(strokes[i]);
        var lastStroke = Stroke_List[Stroke_List.length-1];
        newObjs = processStroke(lastStroke, paper);
    }
    oldObjs = objectCleanUp(oldObjs, newObjs);

    get_strokes();
    get_objects();

});

$(document).ready(function() {
    $(document).on('submit', '#error_report', function() {
        var de = $("#desc").val();
        var da = $("#name").val();
        $.ajax({
            type : 'POST',
            url  : '../php/save_strokes_debug.php',
            data : {
                desc : de,
                name : da,
                points : outputStrokes()
            },
            success :  function(data) {
                alert("success");
                //document.getElementById("successmsg").style.display = 'block';
            }
        });
    return false;
    });
});


function draw_line(pts, idname, type){
    var linepath = pointsToPath(pts);
    var drawn_line = paper.path(linepath);
    drawn_line.id = idname;
    if(type == 'window')
        drawn_line.attr({"stroke": "#0EBFE9", "stroke-width": 5});
    else {
        drawn_line.attr({"stroke": "#000000", "stroke-width": 3});
        drawn_line.mouseout(hoverout);
        drawn_line.mouseover(hovering);
        drawn_line.mousedown(pathMouseDown);
        drawn_line.mouseup(pathMouseUp);
    }
}

function save_line(pts, idnum, idname, type){
    // console.log(0.111);
    var resizeNum = calcResize(lineLength, RESAMPLE_SIZE, RESAMPLE_LEN_PER_SEGMENT);
    Stroke_List.push(new Stroke(idname, idnum, pts, resizeNum, type));
    // console.log(0.112);
}

function isScribble(pts){
    var corners = shortStraw(pts);
    var pL = pathLength(pts, 0, pts.length);
    var dist = distance(pts[0], pts[pts.length-1]);
}

function process_line(pts){
    var idname, type;
    if(windowMode == true)
        type = 'window';
    else if(randomScore(pts, 5) > .075)
        type = 'scribble';
    else
        type = 'stroke';
    idname = type + "_" + lineidcount;
    save_line(pts, lineidcount, idname, type);
    draw_line(pts, idname, type);
    lineidcount++;
}

function drawcorners(stroke){
    var s = stroke.allcorners;
    for(var i=0; i<s.length; i++){
        drawpointmarker(stroke.points[s[i]].x, stroke.points[s[i]].y, "#FF0000");
    }
}

function plotpoints(pts){
    for(var i=0; i<pts.length; i++){
        drawpointmarker(pts[i].x, pts[i].y, "#FF0000");
    }
}

function todegrees(n){
    return n*(180/Math.PI);
}