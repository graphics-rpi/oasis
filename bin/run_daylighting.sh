#!/bin/bash
echo "Content-type: text/html"
echo "=====================Arguments recived===================="
echo "This is daylighting in/output)   $1";
echo "This is month)                   $2";
echo "This is day)                     $3";
echo "This is hour)                    $4";
echo "This is minute)                  $5";
echo "this is weather)                 $6";
echo "this is mode)                    $7";

if [ $# -ne "7" ];
then
    echo "Wrong number of parameters\n"
    echo "Usage: ./run_daylighting <user model folder> <output folder> <month> <day> <hour> <minute> <weather> <visual mode>"
    echo "<br/>"
    echo "Requires trailing /"
    echo "<br/>"
    exit
fi

touch $1/pending.lock

echo "=======================LSVO======================"
# Move where lsvo is built (it contains optix exexutables)
cd /home/grfx/GRAPHICS_GIT_WORKING_REPO/lsvo/build

if [ "$7" = "ncv" ]; then
  # Run lsvo auto direct output to $2
  /home/grfx/GRAPHICS_GIT_WORKING_REPO/lsvo/build/lsvo \
      -i ${1}foo.obj \
      -t 3000 \
      -patches 1000 -offline \
      -remeshend -N -noqt -exp 70 -t $4:$5 \
      -date $2 $3 \
      -d=512x512 \
      -dumpOrthos `ls ${1}*.glcam|wc -l` `ls ${1}*.glcam` \
      -verbose \
      -weather $6 
      #-toodim 0.3 -toobright 0.7 
      #-coordinte 40.783 -73.967 \
      #-d=1024x1024 \

else
  # Run lsvo auto direct output to $2
  /home/grfx/GRAPHICS_GIT_WORKING_REPO/lsvo/build/lsvo \
      -i ${1}foo.obj \
      -t 3000 \
      -patches 1000 -offline \
      -remeshend -N -noqt -exp 70 -t $4:$5 \
      -date $2 $3 \
      -d=512x512 \
      -dumpOrthos `ls ${1}*.glcam|wc -l` `ls ${1}*.glcam` \
      -verbose \
      -weather $6 \
      -toodim 0.3 -toobright 0.7 
      #-coordinte 40.783 -73.967 \
      #-d=1024x1024 \

fi

echo "===============MORGIFY TWEEN HACK================="
# Move back to output folder and make files png and readable
cd $1
mogrify -format png *.ppm
cp ../../tween/foo.obj .
chmod 755 *
cd -
rm $1/pending.lock
touch $1/complete.lock
echo "=============== End Script ======================="
