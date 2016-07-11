var container, scene, camera, renderer, controls;

var geometries = [];
var image_materials = [];
var obj_format = 'NaN';

var widthOffset = -200;
var zOffset = 100;
var widthOffsetIncrement = 100;
var zOffsetIncrement = 100;

function buildMultiviewer(){

    scene = new THREE.Scene();

    var SCREEN_WIDTH = window.innerWidth, SCREEN_HEIGHT = window.innerHeight;
    var VIEW_ANGLE = 45, ASPECT = SCREEN_WIDTH/SCREEN_HEIGHT, NEAR = 0.1, FAR = 20000;
    camera = new THREE.PerspectiveCamera(VIEW_ANGLE, ASPECT, NEAR, FAR);
    scene.add(camera);
    camera.position.set(0, 500, 200);
    camera.lookAt(scene.position);

	renderer = new THREE.WebGLRenderer({antialias: true});

    renderer.setSize(SCREEN_WIDTH, SCREEN_HEIGHT);

    container = document.getElementById('multiviewer');

    container.appendChild(renderer.domElement);

    controls = new THREE.OrbitControls(camera);
    controls.addEventListener('change', render);

    var light = new THREE.PointLight(0xffffff);
    light.position.set(0, 250, 0);
    scene.add(light);


	var models = getModelsToView();
	//var models = ['504', '514', 'fjgmwr', 'fmohhf', 'irrtir', 'kmlenc', 'ontmhz', 'qaswke', 'sumllb', 'suvnqf', 'tfcdgz', 'ukosnc', 'xdwqrh', 'xsucpj', 'yriocd'];
	//I want an equal number of rows of 5 on either side of the origin 
	zOffset *= Math.floor((models.length / 5) / 3);
	models.forEach(function (elm) {
			buildSingleViewer(elm);
		}
	);

	animate();
}

function getModelsToView(){
	var models = new Array();

	$.ajax({
		type: "POST",
		url: "../dev_tools/getIncludedModels.php",
		async: false,
		success: function(e){
			var json = JSON.parse(e);
			models = json.data;
		},
		error: function(e){
			console.error(e)
		}
	});

	return models;
}

function getModelTitle(myid){
    var path="";
    $.ajax({ 
        type: "POST",
        data: {id: myid},
        url: "../php/get_model_from_id.php", 
            async: false, 
            success : function(e) { 
                var json = JSON.parse(e);
                path += json.data; 
            }
    });
    
    return path;
}

function buildSingleViewer(model){           
    var path = "/user_output/geometry/" + model + "/slow/";

	$.ajax({
        url: path + 'foo.obj',
        type: 'HEAD',
        error: function(){
            console.debug("Model: " + model + " not available!");
        },
        success: function(){
        
            objFileContents.fetch(path + "foo.obj");
            objFileContents.parse();    
    		getObjData();
				
			if(widthOffset > 2* widthOffsetIncrement)
			{
				widthOffset = -2 * widthOffsetIncrement;
				zOffset -= zOffsetIncrement;
			}
            
			for (var g = 0; g < geometries.length; g++) {
				var m = new THREE.Mesh(geometries[g], image_materials[g]);
				m.position.set(widthOffset, 0, zOffset);
        		scene.add(m);

    		}  	
    		widthOffset += widthOffsetIncrement;
    		geometries = [];
        }
    });

	return {
	    toggle: function(){
            if(ceiling.side == THREE.FrontSide)
                ceiling.side = THREE.DoubleSide;
            else 
                ceiling.side = THREE.FrontSide;
        }
	}
}

function getObjData() {
    mtl_file_name = objFileContents.vectors[0][1];

    var v  = [];
    var all_vertices = [];

    for (o = 1; o < objFileContents.vectors.length; o++) {

        if (objFileContents.vectors[o][0] == "v") {
            v.push(new Array());

            v[v.length - 1].push(objFileContents.vectors[o][1]);
            v[v.length - 1].push(objFileContents.vectors[o][2]);
            v[v.length - 1].push(objFileContents.vectors[o][3]);

            all_vertices.push(new THREE.Vector3(50 * v[v.length - 1][0], 50 * v[v.length - 1][1], 50 * v[v.length - 1][2]));

        }
        else if (objFileContents.vectors[o][0] == "vt") {
            vt.push(new Array());

            vt[vt.length - 1].push(objFileContents.vectors[o][1] * 0.999);
            vt[vt.length - 1].push(objFileContents.vectors[o][2] * 0.999);
        }
        else if (objFileContents.vectors[o][0] == "vn") {
            vn.push(new Array());
            vn[vn.length - 1].push(objFileContents.vectors[o][1]);
            vn[vn.length - 1].push(objFileContents.vectors[o][2]);
            vn[vn.length - 1].push(objFileContents.vectors[o][3]);
        }
        else if (objFileContents.vectors[o][0] == "f") {
            if(obj_format == "NaN") {
                if( 1 ==  objFileContents.vectors[o][1].split("/").length ) {
                    obj_format = "basic";
                }
                else {
                    obj_format = "redundent";
                }
            }

            if(obj_format == 'basic'  ) {
                var temp_vertex_1 = objFileContents.vectors[o][1];
                var temp_vertex_2 = objFileContents.vectors[o][2];
                var temp_vertex_3 = objFileContents.vectors[o][3];

                if (geometries.length == 0)
                    console.log("ASSERT!!");
                geometries[geometries.length - 1].faces.push(
                new THREE.Face3(
                    temp_vertex_1 - 1,
                    temp_vertex_2 - 1,
                    temp_vertex_3 - 1));
            }
            else {
                var temp_vertex_1 = objFileContents.vectors[o][1];
                var temp_vertex_2 = objFileContents.vectors[o][2];
                var temp_vertex_3 = objFileContents.vectors[o][3];

                temp_vertex_1 = temp_vertex_1.split("/");
                temp_vertex_2 = temp_vertex_2.split("/");
                temp_vertex_3 = temp_vertex_3.split("/");


                if (geometries.length == 0) console.log("ASSERT!!");

                geometries[geometries.length - 1].faces.push(new THREE.Face3(temp_vertex_1[0] - 1, temp_vertex_2[0] - 1, temp_vertex_3[0] - 1));



                geometries[geometries.length - 1].faceVertexUvs[0].push([
                    new THREE.Vector2(vt[temp_vertex_1[0] - 1][1], vt[temp_vertex_1[0] - 1][0]),
                    new THREE.Vector2(vt[temp_vertex_2[0] - 1][1], vt[temp_vertex_2[0] - 1][0]),
                    new THREE.Vector2(vt[temp_vertex_3[0] - 1][1], vt[temp_vertex_3[0] - 1][0])
                ]);
            }
        }
        else if (objFileContents.vectors[o][0] == "usemtl") {
            geometries.push(new THREE.Geometry());		

            geometries[geometries.length - 1].vertices = all_vertices;

            switch(objFileContents.vectors[o][1]) {
                case 'GLASS_1': case 'FILLIN_GLASS_1' :
	                image_materials.push(new THREE.MeshPhongMaterial({transparent:true,opacity: 0.3, color: 0x00FFFF, side:THREE.DoubleSide}));
    	            break;
                case 'floor':
                    image_materials.push(new THREE.MeshBasicMaterial({color: 0xcecece, visible: true}));
                    break;
                case 'furniture':
                    image_materials.push(new THREE.MeshPhongMaterial({color: 0xFFA500, visible: true}));
                    break;
                case 'EXTRA_floor':
                    image_materials.push(new THREE.MeshBasicMaterial({color: 0x000000, wireframe: false}));
                    break;
                case 'FILLIN_ceiling':
                    image_materials.push(new THREE.MeshBasicMaterial({color: 0xcecece, wireframe: false}));
                    ceiling = image_materials[image_materials.length - 1];
                    break;
                case 'EXTRA_wall':
                    image_materials.push(new THREE.MeshLambertMaterial({transparent:true, opacity: 0.4, color: 0xcecece, side: THREE.FrontSide }));
                    break;
                default:
                    image_materials.push(new THREE.MeshPhongMaterial({color:0xcecece, side: THREE.FrontSide , wireframe: false }));
            	    break;
            }
        }
    }
         
    for (var loop = 0; loop < geometries.length; loop++) {
 
        geometries[loop].computeCentroids();
        geometries[loop].computeFaceNormals();

        if(obj_format == "redundent" ){

            geometries[loop].computeVertexNormals();
        }
    }            
}

function animate()
{
	requestAnimationFrame(animate);
	render();
	controls.update();
}

function render()
{
	renderer.render(scene, camera);
}