<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    {{--<meta charset="gbk">--}}

    <meta name="keywords" content="我自己玩,自己玩,己玩,玩">

    <base href="http://www.baidu.com" target="_blank"/>

    {{--<meta http-equiv="refresh" content="1">--}}

    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js?v=2019"></script>
    <script src="https://cdn.bootcss.com/sweetalert/2.1.2/sweetalert.min.js?v=2019"></script>

    <title>菜鸟教程(runoob.com)</title>
</head>
<body>

<div style="opacity:0.5;position:absolute;left:50px;width:300px;height:150px;background-color:#40B3DF"></div>

<div style="font-family:verdana;padding:20px;border-radius:10px;border:10px solid #EE872A;">

    <div style="opacity:0.3;position:absolute;left:120px;width:100px;height:200px;background-color:#8AC007"></div>

    <h3>Look! Styles and colors</h3>

    <div style="letter-spacing:12px;">Manipulate Text</div>

    <div style="color:#40B3DF;">Colors
        <span style="background-color:#B4009E;color:#ffffff;">Boxes</span>
    </div>

    <div style="color:#000000;">and more...</div>

</div>


</body>
</html>

<script>

    $(function () {

        swal("Hello world!");

        swal("Here's the title!", "...and here's the text!");

        swal("Good job!", "You clicked the button!", "success");

        swal({
            title: "Good job!",
            text: "You clicked the button!",
            icon: "success",
        });

        swal({
            title: "Good job!",
            text: "You clicked the button!",
            icon: "success",
            button: "Aww yiss!",
        });

        swal("Good job!", "You clicked the button!", "success", {
            button: "Aww yiss!",
        });

        swal("Click on either the button or outside the modal.")
            .then((value) => {
                swal(`The returned value is: ${value}`);
            });

        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this imaginary file!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    swal("Poof! Your imaginary file has been deleted!", {
                        icon: "success",
                    });
                } else {
                    swal("Your imaginary file is safe!");
                }
            });

        swal("Are you sure you want to do this?", {
            buttons: ["Oh noez!", "Aww yiss!"],
        });

        swal("Are you sure you want to do this?", {
            buttons: ["Oh noez!", true],
        });

        swal("A wild Pikachu appeared! What do you want to do?", {
            buttons: {
                cancel: "Run away!",
                catch: {
                    text: "Throw Pokéball!",
                    value: "catch",
                },
                defeat: true,
            },
        })
            .then((value) => {
                switch (value) {

                    case "defeat":
                        swal("Pikachu fainted! You gained 500 XP!");
                        break;

                    case "catch":
                        swal("Gotcha!", "Pikachu was caught!", "success");
                        break;

                    default:
                        swal("Got away safely!");
                }
            });

        swal({
            text: 'Search for a movie. e.g. "La La Land".',
            content: "input",
            button: {
                text: "Search!",
                closeModal: false,
            },
        })
            .then(name => {
                if (!name) throw null;

                return fetch(`https://itunes.apple.com/search?term=${name}&entity=movie`);
            })
            .then(results => {
                return results.json();
            })
            .then(json => {
                const movie = json.results[0];

                if (!movie) {
                    return swal("No movie was found!");
                }

                const name = movie.trackName;
                const imageURL = movie.artworkUrl100;

                swal({
                    title: "Top result:",
                    text: name,
                    icon: imageURL,
                });
            })
            .catch(err => {
                if (err) {
                    swal("Oh noes!", "The AJAX request failed!", "error");
                } else {
                    swal.stopLoading();
                    swal.close();
                }
            });

    });


</script>
