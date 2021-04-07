{{if url EXISTS}}
    <a href="{{url}}" class="button {{class}}" id="{{id}}" target="{{target || "_self"}}" rel="noreferrer noopener">{{title || "NO TITLE SET"}}</a>
{{else}}
    <button type="button" class="{{class}}" id="{{id}}">{{title || "NO TITLE SET"}}</button>
{{end}}