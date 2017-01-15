<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <link rel="icon" href="media/favicon.png">

        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans:100,200,300,400,600,700" />
    
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OpRuut') }}</title>



        <!-- Local Inline Styles -->
        <style>

            
            body
            {
                background-color: #f5f5f5;
                height: 100%;
                padding: 0px;
                margin: 0px;
                transition: 0.6s ease-in-out 0s;
            }


            .above-the-fold {
                background-color: rgb(41, 128, 185); /* rgb(211, 84, 0); /* redi  */
                width: 100%;
                height: 100%;
                position: relative;

                /*transition: 0.6s ease-in-out 0s;*/

                -webkit-animation: backgroundColoring 5s infinite ease-in-out;
                animation: backgroundColoring 5s infinite ease-in-out;  
            }



            @-webkit-keyframes backgroundColoring {
               0% { background-color: rgb(211, 84, 0); }
              80% { background-color: rgb(41, 128, 185); }
              /*70% { background-color:  rgb(39, 174, 96); }  */
            }

            @keyframes backgroundColoring {
              0% { background-color: rgb(211, 84, 0); }
              80% { background-color: rgb(41, 128, 185); }
              /*70% { background-color:  rgb(39, 174, 96); }  */ 
              
            }


             
            .above-the-fold  .cube-grid-global {
  
                width: 60px;
                height: 60px;
                position: absolute;
                /*margin: 200px auto;*/
                top: 40%;
                left: 50%;
                transform: translate(-25px, 0);

            }

            
            .cube-grid-global .cube-global {
              width: 33%;
              height: 33%;
              background-color: #fff;
              float: left;
              -webkit-animation: sk-cubeGridScaleDelay 1.3s infinite ease-in-out;
                      animation: sk-cubeGridScaleDelay 1.3s infinite ease-in-out; 
            }

            .cube-grid-global .cube1-global {
              -webkit-animation-delay: 0.2s;
                      animation-delay: 0.2s; }
            .cube-grid-global .cube2-global {
              -webkit-animation-delay: 0.3s;
                      animation-delay: 0.3s; }
            .cube-grid-global .cube3-global {
              -webkit-animation-delay: 0.4s;
                      animation-delay: 0.4s; }
            .cube-grid-global .cube4-global {
              -webkit-animation-delay: 0.1s;
                      animation-delay: 0.1s; }
            .cube-grid-global .cube5-global {
              -webkit-animation-delay: 0.2s;
                      animation-delay: 0.2s; }
            .cube-grid-global .cube6-global {
              -webkit-animation-delay: 0.3s;
                      animation-delay: 0.3s; }
            .cube-grid-global .cube7-global {
              -webkit-animation-delay: 0s;
                      animation-delay: 0s; }
            .cube-grid-global .cube8-global {
              -webkit-animation-delay: 0.1s;
                      animation-delay: 0.1s; }
            .cube-grid-global .cube9-global {
              -webkit-animation-delay: 0.2s;
                      animation-delay: 0.2s; }

            @-webkit-keyframes sk-cubeGridScaleDelay {
              0%, 70%, 100% {
                -webkit-transform: scale3D(1, 1, 1);
                        transform: scale3D(1, 1, 1);
              } 35% {
                -webkit-transform: scale3D(0, 0, 1);
                        transform: scale3D(0, 0, 1); 
              }
            }

            @keyframes sk-cubeGridScaleDelay {
              0%, 70%, 100% {
                -webkit-transform: scale3D(1, 1, 1);
                        transform: scale3D(1, 1, 1);
              } 35% {
                -webkit-transform: scale3D(0, 0, 1);
                        transform: scale3D(0, 0, 1);
              } 
            }
      

        </style>



        <!-- Styles -->
        <!-- <link href="/css/app.css" rel="stylesheet"> -->

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

        
        <!-- Webpack Bundle Css -->
        <link href="/assets/vendor.bundle.css" rel="stylesheet">
        <link href="/assets/app.bundle.css" rel="stylesheet">

        <!-- Scripts -->
         <script>
            window.Laravel = <?php echo json_encode([
                'csrfToken' => csrf_token(),
            ]); ?>

            window.ABOVE_THE_FOLD_TIMER = Date.now();

            window.InitialState = <?php echo json_encode([
                'auth' => ['authenticated' => $authenticated, 'userinfo' => $userinfo, 'globalRequests' => $globalRequests, 'error' => $error],
            ]); ?>

        </script>
       
        <script src="//0.0.0.0:6001/socket.io/socket.io.js"></script>

    </head>
    <body>
        
        <div class="above-the-fold"> 
            <div class="cube-grid-global" >
                <div class="cube-global cube1-global" ></div>
                <div class="cube-global cube2-global" ></div>
                <div class="cube-global cube3-global" ></div>
                <div class="cube-global cube4-global" ></div>
                <div class="cube-global cube5-global" ></div>
                <div class="cube-global cube6-global" ></div>
                <div class="cube-global cube7-global" ></div>
                <div class="cube-global cube8-global" ></div>
                <div class="cube-global cube9-global" ></div>
            </div>  
        </div>

        <div id="app">        

            @yield('content')
        
        </div>

        @yield('middle_content')


        @yield('footer_content')

        <!-- Scripts -->
        
        <!-- Webpack Dev assets -->
        <script src="/assets/bootstrap.js" ></script>
        <script src="/assets/vendor.js" ></script>
        <script src="/assets/commons.js" ></script>
        <script src="/assets/app.bundle.js" ></script>
        


        <!-- <script src="/js/bundle.js" ></script> -->

        <!-- <script src="{{ elixir('js/bundle.js') }}"></script> -->
    </body>
</html>
