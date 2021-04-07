<!DOCTYPE html>
<html lang="{{lang}}">
<head>
    <!-- Char set -->
    <meta charset="{{charset}}"/>

    <!-- Browser scale -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=10, user-scalable=yes"/>

    <!-- Title -->
    <title>{{title}}</title>

    <!-- yoast meta -->
    {{yoast}}
</head>
<body>
    {{assets->css}}
    <header>
        <h1>Header</h1>

        {{if navigation->main EXISTS}}
            <nav>
            {{each navigation->main}}
                {{if subnav EXISTS}}
                <span>
                    <button type="button" class="{{class}} item">{{title}}<span>&nbsp;</span></button>
                    <div class="subnav">
                        {{each subnav}}
                            <a href="{{url}}" class="{{class}}" target="{{target || _self}}" rel="noreferrer noopener">{{title}}</a>
                        {{end}}
                    </div>
                </span>
                {{else}}
                <a href="{{url}}" class="{{class}} item" target="{{target || _self}}" rel="noreferrer noopener">{{title}}</a>
                {{end}}

            {{end}}
            </nav>
        {{end}}
    </header>
    {{assets->js}}

    <main>

        <!-- no script message -->
        <noscript>
            <div id="no_script">
                <div class="wrap">
                    <p><strong>Please enable JavaScript</strong> This site will not function correctly without JavaScript enabled.</p>
                </div>
            </div>
        </noscript>