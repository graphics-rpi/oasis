// ============================================
// Debugging main function 
// ============================================

// This function will display all renovations of a specific model
function display_all_renov(username, user_model_num)
{

  // Get the list of ids of the renovations
  $.getJSON(
  	"../php/get_user_renovations_ids.php", 
  	{
      umn: user_model_num, 
      usr: username
    }, 
  	function(e)
  	{
  		// Given the paths_txt
      var renovation_list = e.renov_list;

      for(var i = 0; i < renovation_list.length; i++)
      {

        // Creating the div that will hold these
        var div_id = "div_" + renovation_list[i];
        create_floating_div(div_id)

        raphael_model_viwer(div_id, parseInt(renovation_list[i]), 200);

      }
      // create_2d_view(container_id, paths_txt,size);
    }
  );
}


function create_model_div(new_div_id, user_div_id)
{
  var div = document.createElement(new_div_id);
  div.id = div_id;
  div.style.width  = "100%";
  div.style.height = "300px";
  div.style.float  = "left";
  div.style.position = "relative";
  div.style.background = "white";
  
  // Change eventually
  document.body.appendChild(div);

  // var cont = document.getElementById(user_div_id);
  // cont.appendChild(div);
}

function create_renov_div(new_div_id, model_div_id)
{

  var div = document.createElement(new_div_id);
  div.id = div_id;
  div.style.width  = "200px";
  div.style.height = "200px";
  div.style.float  = "left";
  div.style.position = "relative";
  div.style.background = "white";

  var cont = document.getElementById(model_div_id);
  cont.appendChild(div);
}

function main(){

  all_users = get_users_db();

  for(var i = 0; i < all_users.length; i++){

    cur_username = all_users[i];

    user_div_id = "div_" + cur_username

    

  }




}


// =============================================================================
// Completed Functions
// =============================================================================

function create_2d_view(container_id, paths_txt,size)
{
  var paper = Raphael(container_id, size, size);
  var table = paper.circle(size/2.0, size/2.0, size/2.0 - 5).attr(
  {
    'fill': "white",
    'fill-opacity': 0,
    'stroke': "black",
    'stroke-width': 2
  });

  var radius = table.attr('r');
  var cx = table.attr('cx');
  var cy = table.attr('cy')

  // Spit this file by lines
  var pathfile = paths_txt.split("\n");

  var cur_line = 0;
  var cur_wall = null;

  // Get compass positions
  var compos = pathfile[cur_line++].split(/[ ,]+/);
  var coor   = pathfile[cur_line++].split(/[ ,]+/);
 
  // For each line inside the file
  while (cur_line < pathfile.length)
  {

    var cur_string = pathfile[cur_line];

    // alert("Reading: " + cur_string);

    switch (cur_string)
    {

      case "WALL_ST8":

        // Create wall and bind handlers 

        var s = pathfile[++cur_line].split(" ");
        cur_wall = paper.path("M" + (s[0] * radius + cx) + "," + 
          (s[1] * radius + cy) + "L" + ( s[2] * radius + cx) + "," + 
          ( s[3] * radius + cy) );

        cur_wall.attr(
          {
            'stroke': 'black',
            'stroke-width': 2
        });

        // bind_straight_wall_handlers(cur_wall);

        cur_line++;
        break;

      case "WIN_ST8":

        // Create window and bind handlers
        var t = pathfile[++cur_line].split(" ");
        var cur_win =  paper.path("M" + (t[0] * radius + cx) + "," + 
          (t[1] * radius + cy) + "L" + ( t[2] * radius + cx) + "," + 
          ( t[3] * radius + cy));

        cur_win.attr(
          {
            'stroke': 'lightblue',
            'stroke-width': 4
        });

        cur_line++;
        break;

      case "BED": 

        // Create a bed 
        var ro = create_bed(0,0);
        var ft = paper.freeTransform(ro,
          {keepRatio: true, scale: false, distance: 2, size: 10});

        var u  = pathfile[++cur_line].split(" "); // x_rel, y_rel, angle

        // // Move to the center of canvas
        ft.attrs.translate.x  = (u[0] * radius) + cx;
        ft.attrs.translate.y  = (u[1] * radius) + cy;
        ft.attrs.rotate = u[2];
        ft.apply();
        ft.unplug();


        // cur_line++;
        cur_line++;
        break;

      case "DESK": 

        // Create a desk
        var ro = create_desk(0,0);
        var ft = paper.freeTransform(ro,
          {keepRatio: true, scale: false, distance: 2, size: 10});

        var u  = pathfile[++cur_line].split(" "); // x_rel, y_rel, angle

        // // Move to the center of canvas
        ft.attrs.translate.x  = (u[0] * radius) + cx;
        ft.attrs.translate.y  = (u[1] * radius) + cy;
        ft.attrs.rotate = u[2];
        ft.apply();
        ft.unplug();


        cur_line++;
        break;

      case "WARDROBE":

        // Create a closest 
        var ro = create_closest(0,0);
        var ft = paper.freeTransform(ro,
          {keepRatio: true, scale: false, distance: 2, size: 10});

        var u  = pathfile[++cur_line].split(" "); // x_rel, y_rel, angle

        // // Move to the center of canvas
        ft.attrs.translate.x  = (u[0] * radius) + cx;
        ft.attrs.translate.y  = (u[1] * radius) + cy;
        ft.attrs.rotate = u[2];
        ft.apply();
        ft.unplug();

        cur_line++;
        break;

      case "SKYLIGHT":

        // Create a skylight 
        var ro = create_skylight(0,0);
        var ft = paper.freeTransform(ro,
          {keepRatio: true, scale: false, distance: 2, size: 10});

        var u  = pathfile[++cur_line].split(" "); // x_rel, y_rel, angle

        // // Move to the center of canvas
        ft.attrs.translate.x  = (u[0] * radius) + cx;
        ft.attrs.translate.y  = (u[1] * radius) + cy;
        ft.attrs.rotate = u[2];
        ft.apply();
        ft.unplug();

        cur_line++;
        break;
        
      case "END":
        cur_line++;
        break;

      case "":
        cur_line++;
        break;

      default:
        console.log("ERROR: Reading path file syntax broken");
    }
  }
}

function raphael_model_viwer(container_id, model_id, size)
{
  // We must first get data from the database
  $.getJSON(
    "../php/get_paths_txt.php", 
    {id: model_id}, 
    function(e)
    {
      // Given the paths_txt
      var paths_txt = e.paths_txt;
      // alert("Things")
      create_2d_view(container_id, paths_txt,size) 
    }
  );
}


// =============================================================================
// TODO functions
// =============================================================================

function get_users_db()
{
  // returns a list of users from our database
  user_list = new Array();
  return user_list;
}


function create_user_div(div_id)
{
  // creates a div container with that id
  // this container is 100% width
}


function get_models(username)
{
  // returns a list of user_model_num from a given username
  user_model_num_list = new Array();
  return user_model_num_list;
}

function create_model_div(div_id, container_id, username,  user_model_num)
{
  // see notes
  // returns nothing just create 2 divs
}

function get_renovations(username, user_model_num)
{
  //  returns a list of ids which corespond to specific model
  id_list = new Array();
  return id_list;
}