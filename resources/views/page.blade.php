<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$title}}</title>
    <style type="text/css">
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 20px;
            font-family: Helvetica, Arial;
            color: {{ $font_color }};
            background: {{ $bg }};
        }
        .profileImage img {
            width: auto;
            height: 100px;
        }
        .profileTitle {
            font-size: 17px;
            font-weight: bold;
            margin-top: 10px;
        }
        .profileDescription {
            font-size: 15px;
            margin-top: 5px;
        }
        .linkArea {
            width: 100%;
            margin: 50px 0;
        }
        .linkArea a {
            display: block;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .linkArea a.linksquare {
            border-radius: 0px;
        }
        .linkArea a.linkrounded {
            border-radius: 50px;
        }
        .banner a {
            color: {{ $font_color }};
        }
    </style>
</head>
<body>
    <div class="profileImage">
        <img src="{{ $profile_image }}" alt="" />
    </div>

    <div class="profileTitle">{{ $title }}</div>
    <div class="profileDescription">{{ $description }}</div>

    <div class="linkArea">

        @foreach($links as $link)
            <a
                href="{{$link->href}}"
                class="link{{$link->op_border_type}}"
                style="background-color: {{$link->op_bg_color}};color: {{$link->op_text_color}};"
                target="_blank"
            >{{ $link->title }}</a>
        @endforeach

    </div>

    <div class="banner">
        Feito com ♥ por <a href="">B7Web</a>
    </div>

    @if (!empty($fb_pixel))
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):nqueue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '{{$fb_pixel}}');
                fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" 
        src="https://www.facebook.com/tr?=id={{$fb_pixel}}&ev=PageView&noscript=1"
        /></noscript>
    @endif
</body>
</html>