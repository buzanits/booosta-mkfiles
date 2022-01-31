{BBOXCENTER}
{BPANEL|paneltitle::Edit {Name}}

{BFORMSTART|{name}}
{HIDDEN|action|editdo}
{HIDDEN|object_id|{%{idfield}}}
{HIDDEN|form_token|{%form_token}}

{rows}

{BFORMSUBMIT|class::center-block}
{BFORMEND}
{BLINK|Back|javascript:history.go(-1);}

{/BPANEL}
{/BBOXCENTER}

%if({%subtables_in_edit}):
  {BBOXCENTER|bboxsize::12}
  {BPANEL|paneltitle::{Subname}}

  {BLINKADD|New {subtable}|{subscript}/new/{%{idfield}}}

  {%subliste}
  {/BPANEL}
  {/BBOXCENTER}
%endif;
