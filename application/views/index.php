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
        #chart {
            height: 5000px;
            display: inline-flex; /* Inline flex to fit content width */
            background-color: #141414;
        }
        .candle {
            width:3px;
            position: relative;
        }

        .candle:hover {
            background-color: black;
        }
        
        .stick {
            position: absolute;
            width: 1px;
            left: 1px;
        }
        .body {
            position: absolute;
            width: 2px;
            left: 0;
        }

        .bear .stick, .bear .body {
            background-color: red;
        }
        .bull .stick, .bull .body {
            background-color: green;
        }

        #content {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            cursor: grab;
            position: relative;
        }
        #content:active {
            cursor: grabbing;
        }

        #control {
            position: absolute;
            bottom: 0;
            right: 0;
            height: 100vh;
            width: 15px;
        }

        #control .card {
            width:100%;
            height:100%;
            cursor: n-resize;
        }
    </style>
</head>
<body>
    <h1>Hello, world!</h1>
    <div id="wrapper" class="position-relative">
        <div id="content">
            <div id="chart">
                <div style="width:100vw" class="spacers"></div>
            </div>
        </div>
        <div id="control">
            <div class="card">

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Fetch data and populate the chart
            $.get("<?= base_url("index.php/") ?>api/price", function(data, status) {
                const factor = 50;
                data.forEach(function(e) {
                    const bottom = e[3] / factor;
                    const height = (e[2] - e[3]) / factor;

                    const open = e[1] / factor;
                    const close = e[4] / factor;

                    const newElement = $("<div class='candle'><div class='stick'></div><div class='body'></div></div>");
                    newElement.find(".stick").css({
                        bottom,
                        height
                    });

                    let color = "bull";
                    if (open > close) {
                        color = "bear";
                        newElement.find(".body").css({
                            bottom: close,
                            height: open - close
                        });
                    } else {
                        newElement.find(".body").css({
                            bottom: open,
                            height: close - open
                        });
                    }

                    newElement.addClass(color);
                    $("#chart").prepend(newElement);
                });

                $('#content')[0].scrollLeft = $("#chart").width()-$('#content').width()*1.5;
                $('#content')[0].scrollTop = ($("#chart").height()-data[0][3]/factor)*0.9;
            });

            // Scroll by dragging
            let isDragging = false;
            let startX, startY, scrollLeft, scrollTop;

            $('#content').on('mousedown', function(e) {
                isDragging = true;
                startX = e.pageX - this.offsetLeft;
                startY = e.pageY - this.offsetTop;
                scrollLeft = this.scrollLeft;
                scrollTop = this.scrollTop;
                $(this).css("cursor", "grabbing");
            });

            $(document).on('mouseup', function() {
                isDragging = false;
                $('#content').css("cursor", "grab");
            });

            $('#content').on('mousemove', function(e) {
                if (!isDragging) return;
                e.preventDefault();
                const x = e.pageX - this.offsetLeft;
                const y = e.pageY - this.offsetTop;
                const walkX = (x - startX) * 2; // scroll speed multiplier
                const walkY = (y - startY) * 2; // scroll speed multiplier
                this.scrollLeft = scrollLeft - walkX;
                this.scrollTop = scrollTop - walkY;
            });

            
            let isResizing = false;
            let startPos;

            $('#control .card').on('mousedown', function(e) {
                isResizing = true;
                startPos = e.pageY - this.offsetTop;
            });
            
            $(document).on('mousemove', function(e) {
                if (!isResizing) return;
                e.preventDefault();
                let newPos = startPos - (e.pageY -  $('#control .card')[0].offsetTop);
                startPos = e.pageY -  $('#control .card')[0].offsetTop;
                resizeChart(newPos)
            });
            
            $(document).on('mouseup', function(e) {
                isResizing = false;
            });
            
            function resizeChart(f){
                const fac = Math.pow(1.001, f)
                $(".candle").each(function(){
                    let stickHeight = $(this).find(".stick").height();
                    let stickBot = parseFloat($(this).find(".stick").css("bottom"));
                    
                    let bodyHeight = $(this).find(".body").height();
                    let bodyBot = parseFloat($(this).find(".body").css("bottom"));
                    
                    
                    $(this).find(".stick").css({
                        height: stickHeight *fac,
                        bottom: stickBot *fac
                    })
                    
                    $(this).find(".body").css({
                        height: bodyHeight *fac,
                        bottom: bodyBot *fac
                    })
                })
                let scrolls = $("#chart").height() - ($("#chart").height()-$('#content')[0].scrollTop)*fac;
                $('#content')[0].scrollTop = scrolls;
                console.log(scrolls)
            }
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
