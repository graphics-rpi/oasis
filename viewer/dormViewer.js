var canvas;

var scenes = [], renderer;

var geometries = [];
var image_materials = [];
var obj_format = 'NaN';

function buildMultiviewer(){

	var models = getModelsToView();
	//var models = ['504', '514', 'fjgmwr', 'fmohhf', 'irrtir', 'kmlenc', 'ontmhz', 'qaswke', 'sumllb', 'suvnqf', 'tfcdgz', 'ukosnc', 'xdwqrh', 'xsucpj', 'yriocd'];

    var modelsArray = [];

    for(var k in models){
        if(models.hasOwnProperty(k)){
            modelsArray.push({key: k, val:models[k]});
        }
    }

    modelsArray.sort(function(a, b){
        return a.val.length - b.val.length;
    });
    modelsArray.reverse();
    for(var key in modelsArray){
        var modelList = modelsArray[key].val;
        modelList.forEach(function(elm){
            buildSingleViewer(elm, modelsArray[key].key);
        });
    }

    renderer = new THREE.WebGLRenderer({canvas:canvas, antialias:true});
    renderer.setClearColor(0xffffff, 1);
    renderer.setPixelRatio(window.devicePixelRatio);

	animate();
}

function getModelsToView(){
	var models = new Array();

	$.ajax({
		type: "POST",
		url: "getIncludedModels.php",
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

function buildSingleViewer(model, dorm){      
    canvas = document.getElementById("c");

    var template = document.getElementById("template").text;
    var content = document.getElementById("content");

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

            var scene = new THREE.Scene();

			for (var g = 0; g < geometries.length; g++) {
				var m = new THREE.Mesh(geometries[g], image_materials[g]);

                scene.add(m);
    		}  	
                
            var element = document.createElement("div");
            element.className = "list-item";
            element.innerHTML = template.replace('$', dormLookup(dorm));
            console.log(template);

            scene.userData.element = element.querySelector(".scene");
            content.appendChild(element);

            var camera = new THREE.PerspectiveCamera(30, 1, 1, 200);
            camera.position.set(0, 80, 60);
            camera.lookAt(scene.position);
            scene.userData.camera = camera;

            var controls = new THREE.OrbitControls(scene.userData.camera, scene.userData.element);
            controls.enableZoom = false;
            scene.userData.controls = controls;


            scene.add(new THREE.HemisphereLight(0xaaaaaa, 0x444444));
                
            var light = new THREE.DirectionalLight(0xffffff, 0.5);
            light.position.set(1,1,1);
            scene.add(light);

            scenes.push(scene);

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
 
        //geometries[loop].computeCentroids();
        geometries[loop].computeFaceNormals();

        if(obj_format == "redundent" ){

            geometries[loop].computeVertexNormals();
        }
    }            
}

function animate()
{
    render();
	requestAnimationFrame(animate);

    scenes.forEach(function(scene)
    {
        scene.userData.controls.update();
    });
}

function render()
{
	updateSize();

    renderer.setClearColor(0xffffff);
    renderer.setScissorTest(false);
    renderer.clear();

    renderer.setClearColor(0xe0e0e0);
    renderer.setScissorTest(true);

    scenes.forEach(function(scene)
    {
        var element = scene.userData.element;

        var rect = element.getBoundingClientRect();

        if(rect.bottom < 0 || rect.top > renderer.domElement.clientHeight ||
            rect.right < 0 || rect.left > renderer.domElement.clientWidth) 
        {
            return;
        }

        var width = rect.right - rect.left;
        var height = rect.bottom - rect.top;
        var left = rect.left;
        var bottom = renderer.domElement.clientHeight - rect.bottom;

        renderer.setViewport(left, bottom, width, height);
        renderer.setScissor(left, bottom, width, height);

        var camera = scene.userData.camera;

        renderer.render(scene, camera);
    });
}

function updateSize()
{
    var width = canvas.clientWidth;
    var height = canvas.clientHeight;

    if(canvas.width != width || canvas.height !=height)
    {
        renderer.setSize(width, height, false);
    }
}

function dormLookup(dorm)
{
    console.log(dorm);
    switch(dorm){
        case 'barh':
            return 'BARH';
            break;
        case 'rahp_apt':
            return 'RAHP B';
            break;
        case 'barton':
            return 'Barton Hall';
            break;
        case 'blitman':
            return 'Blitman Residence Commons';
            break;
        case 'bray':
            return 'Bray Hall';
            break;
        case 'bryckwyck':
            return 'Bryckwyck';
            break;
        case 'cary':
            return 'Cary Hall';
            break;
        case 'colonie':
            return 'Colonie Apartments';
            break;
        case 'commons':
            return 'Commons';
            break;
        case 'crockett':
            return 'Crockett Hall';
            break;
        case 'davison':
            return 'Davison Hall';
            break;
        case 'e_complex':
            return 'E-Complex';
            break;
        case 'hall':
            return 'Hall Hall';
            break;
        case 'nason':
            return 'Nason Hall';
            break;
        case 'north':
            return 'North Hall';
            break;
        case 'nugent':
            return 'Nugent Hall';
            break;
        case 'quad':
            return 'Quadrangle (The Quad)';
            break;
        case 'sharp':
            return 'Sharp Hall';
            break;
        case 'rahp_single':
            return 'Single RAHP';
            break;
        case 'stacwyck':
            return 'Stacwyck Apartments';
            break;
        case 'warren':
            return 'Warren Hall';
            break;
        default:
            return 'Other';
    }
}