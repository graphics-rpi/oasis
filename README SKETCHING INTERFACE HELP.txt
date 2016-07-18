README FOR SKETCH BASED INTERFACE
By: Eric Zhang
for further help email ezhang2008@gmail.com

The sketch based interface was created by me (eric) in the fall 2015/spring 2016/summer 2016 as an add-on to OASIS
(made by Max Espinoza). Hopefully this document will help anyone else understand the convoluted logic of this
application.

The application is built completely in Javascript (I'm so sorry), and uses HTML5 canvas, RaphaelJS, and FreeTransform
(for Raphael). At the time of this writing, there are a ton of global variables, pretty much all of them listed at the
top of the sketchpad.js file. The most important are Stroke_List and Rectangles. Those 2 are arrays that
contain all of the main data of the program. They are all arrays of objects Stroke and RectangleObject, respectively.
I think the functions and variables within each of the object declarations are fairly self explanitory.

The program works like this: (function names will be followed by '()', without arguements)

First, the user enters a stroke. This is recorded by the canvas, under the variable name 'sketchpadPaper'. The actions
for mouse can be found under sketchpad.mouseup, mousedown, etc. The stroke is processed using findPrintedPath(), and 
process_line(). Each stroke is categorized into 3 categories: a scribble, a recognized letter, or a normal stroke. The
type is line is determined in process_line() function.

A scribble's purpose is for scribbling out previously drawn strokes. The logic for scribbling is contained within
scribbleOut().

A recognized letter is determined by the $N recognizer. In the recognizer template dataset, there are templates for the
letters S, W, B, and D for first letters of each of the furniture. The recognizer returns a classification and score 
given a set of points. If the score does not meet a threshold, it's considered a normal line. The classification of
a letter is used to reclassify objects to another furniture item. THe logic for reclassification is under the function
reclassify().

After each stroke, the program reprocesses the entire canvas. This is done using rectangleScore() (to score combinations of
strokes), and chooseBestRectangles() (to choose the best rectangles). Afterwards, to fit a rectangle to combination of
strokes, we use iterativeScoring() to iteratively grow (or shrink) a reectangle to fit the strokes.

The canvas loads a previous file using the load_sketch_sketchpad() function, but the logic is contained within loadFile().

The canvas contents are exported using the exportStrokes() function. It is used in the sketching_ribbon_handler() function
located in the sketching_ui file.

best of luck understanding this program, I hope you can improve this

Eric