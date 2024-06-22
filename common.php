<?php

$fileDirectory = $punnyAddress;

require("./php/common_query_param.php");

require("./php/common_load_menu_html.php");

require("./php/common_canonical.php");

require("./php/common_load_target_html.php");

require("./php/common_image.php");

require("./php/common_vc_runtime.php");

require("./php/common_syntax_hilight.php");

require("./php/common_mathjax.php");

require("./php/common_file_last_modify.php");

require("./php/common_style_highlight_page.php");

require("./php/common_style_override.php");



// トップページのテンプレートの読み込み
$strIndexTemplate = file_get_contents($indexFileName);

require("./php/common_title.php");

require("./php/common_btn_prev_next.php");

require("./php/common_css_js_filetime.php");

require("./php/common_last_modify.php");


// index内にある、スタイル、コンテンツ、階層の開きをそれぞれ、具体的な文字列へと置き換える
require("./php/common_replace_params.php");

// トップページとして表示
echo ($strIndexEvaluated);
