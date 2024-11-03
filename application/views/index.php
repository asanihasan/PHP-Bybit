<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <title>Hello, world!</title>
    <style>
        /* Main draggable element */
        .draggable {
            width: auto;
            height: auto;
            background-color: #f0f0f0;
            border: 2px solid #ccc;
            position: absolute;
            top: 100px;
            left: 100px;
            cursor: move;
        }

        /* Drag handle inside the main element */
        .drag-handle {
            background-color: #333;
            color: white;
            padding: 0;
            cursor: grab;
        }

        /* Content area inside the draggable element */
        .content {
            padding: 0;
        }

        .resizable {
            width: 200px;
            height: 150px;
            background-color: #f0f0f0;
            border: 2px solid #ccc;
            padding: 10px;
            box-sizing: border-box;
        }
        #chart {
            height: 1000px;
            display: inline-flex; /* Inline flex to fit content width */
            background-color: #141414;
        }
        .candle {
            width:5px;
            position: relative;
        }
        .stick {
            position: absolute;
            width: 1px;
            left: 1px;
        }
        .body {
            position: absolute;
            width: 3px;
            left: 0;
        }

        .bear .stick, .bear .body {
            background-color: red;
        }
        .bull .stick, .bull .body {
            background-color: green;
        }
    </style>
  </head>
  <body>
    <h1>Hello, world!</h1>
    <div id="chart">
        <div style="width:100vw" class="spacers">

        </div>
    </div>
    <!-- <div class="draggable" id="draggable">
        <div class="drag-handle dragHandle">Drag me!</div>
        <div class="content">
            <div class="resizable">
                <p>Resize me from the edges or corners!</p>
            </div>
            <div class="resizable">
                <p>Resize me from the edges or corners!</p>
            </div>
        </div>
    </div> -->


    <script>
        $(document).ready(function() {
            $.get("<?= base_url("index.php/") ?>api/price", function(data, status){
                data.forEach(function(e){
                    const bottom = e[3]/100
                    const height = (e[2] - e[3])/100
                    
                    const open = e[1]/100
                    const close = e[4]/100

                    const newElement = $("<div class='candle'><div class='stick'></div><div class='body'></div></div>");
                    newElement.find(".stick").css({
                        bottom,
                        height
                    })

                    let color = "bull"
                    if(open > close){
                        color = "bear"
                        newElement.find(".body").css({
                            bottom: close,
                            height: open-close 
                        })
                    } else {
                        newElement.find(".body").css({
                            bottom: open,
                            height: close-open
                        })
                    }

                    newElement.addClass(color);
                    $("#chart").prepend(newElement);
                })
            });

            // $(".resizable").resizable();

            // let offsetX = 0, offsetY = 0;

            // // Mouse down event on drag handle
            // $(".dragHandle").on("mousedown", function(e) {
            //     // Prevent default behavior
            //     e.preventDefault();

            //     // Calculate initial offsets
            //     const draggable = $("#draggable");
            //     offsetX = e.pageX - draggable.position().left;
            //     offsetY = e.pageY - draggable.position().top;

            //     // Mouse move and mouse up handlers
            //     $(document).on("mousemove.dragging", function(e) {
            //         // Update position of the draggable element
            //         draggable.css({
            //             left: e.pageX - offsetX,
            //             top: e.pageY - offsetY
            //         });
            //     });

            //     $(document).on("mouseup.dragging", function() {
            //         // Remove the move and up events when dragging stops
            //         $(document).off("mousemove.dragging mouseup.dragging");
            //     });
            // });
        });
    </script>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    -->
  </body>
</html>
