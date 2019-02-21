<!DOCTYPE HTML>
<html lang="en-US">

<head>
    <meta charset="utf-8">
    <script src="https://libs.baidu.com/jquery/2.1.4/jquery.min.js"></script>

    {{--轮播图--}}
    <script type="text/javascript" src="{{asset('unslider/unslider.min.js')}}"></script>

    {{--富文本--}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>

    {{--bootstrap--}}
    {{--<link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">--}}
    {{--<script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>--}}

    {{--sweetalert--}}
    <script src="https://cdn.bootcss.com/sweetalert/2.1.2/sweetalert.min.js?v=2019"></script>

    {{--mycss--}}
    <link rel="stylesheet" type="text/css" href="{{asset('css/en_web/mycss.css')}}">

</head>

<body>
{{csrf_field()}}

<div class="web_pic">
    <img src="http://static.iissbbs.com/images/yuanxiao/head_logo.png" />
</div>

<div class="web_title">
    <a>Home</a>
    <a>News</a>
    <a>Business</a>
    <a>Travel</a>
    <a>Photo</a>
    <a>Video</a>
    <a>Learning China</a>
    <a>Voices</a>
</div>

<div class="web_hr_solid"></div>

<div class="body_box">
    <div class="body_box_left">

        <h3 style="padding-left: 50px;padding-right: 50px;">{{trim($data['title'],"'")}}</h3>

        <div style="padding-left: 50px;padding-bottom: 20px;font-size: 12px;color: gray">
            <span>{{trim($data['time'],"'")}}</span>
            <span style="padding-left: 20px;">战略网</span>
            <span style="padding-left: 20px;">杜仲</span>
        </div>

        <div class="web_hr_dashed"></div>

        <div class="news_content">

            @foreach($data['pic'] as $pic)

                <img src="{{$pic}}">

            @endforeach

            @foreach($data['content'] as $p)

                <p>{{$p}}</p>

            @endforeach
        </div>

        <div id="mycomment" style="padding-left: 50px;position: relative;">

        </div>

        <div style="padding-left: 50px;position: relative;">
            <textarea id="pinglun"></textarea>
            <a class="mybtn1 mybtn2" onclick="success();" style="float: right;position: absolute;left: 700px;top: 8px;width: 100px;z-index: 1">评论</a>
        </div>

        <div class="body_box_down">

            <div class="web_hr_solid"></div>

            <h3 style="padding-left: 50px;color: darkred;font-size: 20px">Related news</h3>

            @if (!empty($related))

                <div class="banner" id="b03">
                    <ul>

                        @foreach($related as $img)

                            <li><a href="{{url('/test/'.$img['uuid'])}}"><img src="{{$img['pic']}}" alt="" width="640" height="380" ><p>{{$img['title']}}</p></a></li>

                        @endforeach

                    </ul>
                </div>

            @endif

        </div>

        <div class="web_hr_solid"></div>


    </div>
    <div class="body_box_right">
        <!-- 广告位 -->
        <div class="ad1" style="overflow: hidden">
            {{--<script type="text/javascript" src="//baidujs1.iissbbs.com/common/wuevj.js?gb=taktdhc"></script>--}}

            <h3 style="padding-left: 50px;padding-top: 32px;color: red;font-size: 20px">Most popular in 24h</h3>

            <div class="web_hr_dashed"></div>

            <div style="padding-left: 50px">
                <ul>
                    @for($i=0;$i<=10;$i++)

                        <li><a><h5>{{str_random('30')}}</h5></a></li>

                    @endfor
                </ul>
            </div>

            <hr>

        </div>

        <div class="ad2" style="overflow: hidden;padding-top: 20px">
            {{--<script type="text/javascript" src="//baidujs1.iissbbs.com/source/d4fmq3.js?lg=yfpdivi"></script>--}}
        </div>
    </div>
    <div class="body_box_down">
        <div style="float: left">
            <img style="float: left" src="http://static.iissbbs.com/images/yuanxiao/head_logo.png" />
        </div>
        <div style="float: left;font-size: 12px;padding-left: 200px">
            <p>Copyright ©1999-2019 Chinanews.com. All rights reserved.</p>
            <p>Reproduction in whole or in part without permission is prohibited.</p>
        </div>
    </div>

</div>







</body>

<script type="text/javascript">

    $(document).ready(function(e) {

        $('#b03').unslider({dots: true});

        $text=new SimpleMDE({
            element: document.getElementById("pinglun"),
            spellChecker: false,
        });

    });

    function success() {

        var url='/data/ajax';
        var data={

            _token :$("input[name=_token]").val(),
            type   :'commit',
            key    :$text.value()

        };

        $.post(url,data,function (response){

            $("<div style='background-color: gainsboro;'>"+response.data+"</div>").appendTo($("#mycomment"));

        },'json');

        return;

        swal({
            title: "评论成功",
            icon: "success",
        })
            .then((value) => {
                //console.log(value);
                //location.reload();
            });

    }

</script>
</html>
