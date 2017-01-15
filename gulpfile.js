// Load the user gulp tasks and run
var gulpUserTasks = require('./gulp/tasks');
 
gulpUserTasks();



// Load the Elixir tasks and run them
var elixirTask = require('./gulp/elixirTask');
 
elixirTask();

