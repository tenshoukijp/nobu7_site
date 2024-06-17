<?php

function getQueryParam($query_name)
{
    return filter_input(INPUT_GET, $query_name);
}


$urlParamPage = getQueryParam('page');
$orgParamPage = getQueryParam('page');


// rel=canonical の設定
if ( $urlParamPage != "" ) {

    $strCanonicalKey = $urlParamPage;
    // 一番最後の2があるキーは、2が無いものとhtmlを比べて、同じhtmlを示しているならば、
    // 2が無い方が正規のページ。これはgoogle検索で「ページが重複している」といった判定になるのを避けるため。

	// "2"で終わっているか判定
	if (substr($urlParamPage, -1) === "2") {
	  // "2"を取り除いた文字列
	  $urlParamPageWithout2 = substr($urlParamPage, 0, -1);

	  $html2 = $content_hash[$urlParamPage]['html'];          // 2がある方のHTML
	  $html1 = $content_hash[$urlParamPageWithout2]['html'];  // 2が無い方のHTML

	  // 両方有効なhtmlをもっており、しかも同じならば
	  if (isset($html1) && isset($html2) && ($html1 == $html2)) {
          // html1(2というのが無い方)を採用する。
          $strCanonicalKey = $urlParamPageWithout2;
	  }
	}


    $strCanonical = "https://" . $punnyAddress . "/?page=" . $strCanonicalKey;
} else {
    $strCanonical = "https://" . $punnyAddress . "/";
}


// デフォルトのページ
if ($urlParamPage == "") {
    $urlParamPage = $defaultHomePage;
}

if (!array_key_exists($urlParamPage, $content_hash)) {
    $urlParamPage = "401";
}

/*
if ($_SERVER['HTTP_HOST'] == "usr.s602.xrea.com") {
    if (str_contains($_SERVER["REQUEST_URI"], "/" . $punnyAddress)) {
        $OldRequest = $_SERVER["REQUEST_URI"];
        $NewRequest = str_replace("/".$punnyAddress, "", $OldRequest);
        $NewUrl = "https://" . $punnyAddress . $NewRequest;
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: " . $NewUrl);
        exit;
    }
}
*/

$strMenuTemplate = file_get_contents("menu.html");
// まずBOMの除去
$strMenuTemplate = preg_replace('/^\xEF\xBB\xBF/', '', $strMenuTemplate);


$strPageFileFullPath = $content_hash[$urlParamPage]['html'];

// コンテンツのページテンプレート読み込み
$strPageTemplate = file_get_contents($strPageFileFullPath);


// まずBOMの除去
$strPageTemplate = preg_replace('/^\xEF\xBB\xBF/', '', $strPageTemplate);


// eclipseのオートフォーマッタが変な所で改行するので対応
$strPageTemplate = preg_replace('/<img\s+src/ms', '<img src', $strPageTemplate);


// widthやheightが無いイメージタグにマッチしたら、widthやheightを入れる。
$strPageTemplate = preg_replace_callback(
    "/(<img src=[\"'])([^\"']+?)([\"'])((\s+class=[\"'][^\"']+?[\"'])?(\s+attr=[\"']noedge[\"'])?(\s+align=[\"'][a-z]+[\"'])?(\s+attr=[\"']noedge[\"'])?>)/",
    function ($matches) {
        // httpが含まれている。
        if (strpos($matches[0], 'http') !== false) {
            return $matches[0];

            // サイト内の画像
        } else {
            $strTargetImageFileName = "/virtual/usr/public_html/" . $punnyAddress . "/" . $matches[2];
            $strFullPathInfo = pathinfo($strTargetImageFileName);
            $strPathInfoDirName = $strFullPathInfo["dirname"];
            $strPathInfoBaseName = $strFullPathInfo["basename"];
            $str2xTargetImageFileName = $strPathInfoDirName . "/2x/2x_" . $strPathInfoBaseName;
            if (file_exists($str2xTargetImageFileName)) {
                $timeTargetImageUpdate = filemtime($str2xTargetImageFileName);
                $strTargetImageUpdate = date("YmdHis", $timeTargetImageUpdate);
                list($width, $height, $type, $attr) = getimagesize($strTargetImageFileName);
                $strTargetUrlPath = str_replace($strPathInfoBaseName, "2x/2x_" . $strPathInfoBaseName, $matches[2]);
                return $matches[1] . $strTargetUrlPath . "?v=" . $strTargetImageUpdate . $matches[3]  . " alt=\"PICTURE\" " . $attr . $matches[4];
            } else {
                $timeTargetImageUpdate = filemtime($strTargetImageFileName);
                $strTargetImageUpdate = date("YmdHis", $timeTargetImageUpdate);
                list($width, $height, $type, $attr) = getimagesize($strTargetImageFileName);
                return $matches[1] . $matches[2] . "?v=" . $strTargetImageUpdate . $matches[3]  . " alt=\"PICTURE\" " . $attr . $matches[4];
            }
        }
    },
    $strPageTemplate
);


$vsr_array_style  = array("%(vs2013runtime)s", "%(vs2015runtime)s", "%(vs2017runtime)s", "%(vs2019runtime)s", "%(vs2022runtime)s");
$vsarray_template = array(
    'https://www.microsoft.com/ja-jp/download/details.aspx?id=40784',
    'https://support.microsoft.com/ja-jp/help/2977003/the-latest-supported-visual-c-downloads',
    'https://support.microsoft.com/ja-jp/help/2977003/the-latest-supported-visual-c-downloads',
    'https://support.microsoft.com/ja-jp/help/2977003/the-latest-supported-visual-c-downloads',
    'https://support.microsoft.com/ja-jp/help/2977003/the-latest-supported-visual-c-downloads'
);
$strPageTemplate = str_replace($vsr_array_style, $vsarray_template, $strPageTemplate);



$strShCoreHeader = "";
$strShCoreFooter = "";

$strShcoreCSSUpdate = "";

// このbrush:があれば、シンタックスハイライトする必要があるページ。上部と下部に必要なCSSやJSを足しこむ
if (strpos($strPageTemplate, "brush:") != false) {
    $strShCoreHeader = '<link rel="stylesheet" type="text/css" href="./hilight/styles/shcore-3.0.83.min.css?v=%(shcorecssupdate)s">';

    // shcoreのスタイルシート
    $timeShcoreCSSUpdate = filemtime("./hilight/styles/shcore-3.0.83.min.css");
    $strShcoreCSSUpdate = date("YmdHis", $timeShcoreCSSUpdate);

    $strShCoreFooter = "<script type='text/javascript' src='./hilight/scripts/shcore-3.0.97.min.js'></script>\n" .
        "<script type='text/javascript'>\n" .
        "SyntaxHighlighter.defaults['toolbar'] = false;\n" .
        "SyntaxHighlighter.defaults['auto-links'] = false;\n" .
        "SyntaxHighlighter.all();\n" .
        "</script>\n";
}
//-------------- シンタックスハイライター系ここまで --------------


// "%(hilight)s"があれば置き換え
// ソースハイライト用。"%(hilight)s"がある時だけ埋め込まれる
$strHilightJS = "";
$strPageTemplate = str_replace("%(hilight)s", $strHilightJS, $strPageTemplate);

// シンタックスハイライター


// MATHJAX
$strMathJaxCoreFooter = "";

// 必要であれば、maxjaxを機能させる。
if (strpos($strPageTemplate, "(mathjax)s") != false) {
    $strMathJaxCoreFooter = <<<MATHJAX
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
    tex2jax: {
    inlineMath: [['$','$']],
    displayMath: [['$$','$$']],
    processEscapes: true,
    processClass: "asciimath2jax_process",
    ignoreClass: "sitebody"
    },
    CommonHTML: { matchFontHeight: false }
});
</script>
<script type="text/javascript" async
    src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-AMS_CHTML">
</script>
MATHJAX;

    $strMathJaxJS = "";
    $strPageTemplate = str_replace("%(mathjax)s", $strMathJaxJS, $strPageTemplate);
}

// MATHJAX



// ファイルのアーカイブがあれば、更新日時へと置き換え
$fileArchieve = "";
if (isset($filetime_hash[$urlParamPage])) {
    $fileArchieve = $filetime_hash[$urlParamPage];
}
if ($fileArchieve) {
    $filetime = filemtime($fileArchieve);
    $fileKeys   = array("%(file)s", "%(year)04d", "%(mon)02d", "%(mday)02d");
    $fileValues = array($fileArchieve, date("Y", $filetime), date("m", $filetime), date("d", $filetime));

    $strPageTemplate = str_replace($fileKeys, $fileValues, $strPageTemplate);
}


// css系を置き換え
$strStyleTemplate = file_get_contents("style_dynamic.css");

$normalizeUrlParamPage = $urlParamPage; // 普通のページならそのまま?page=○○○ の部分へと置き換える

// 今表示しているページへの太字等
$strStyleTemplate = str_replace("%(menu_style_dynamic)s", $normalizeUrlParamPage, $strStyleTemplate);

// トップページのテンプレートの読み込み
$strIndexTemplate = file_get_contents($indexFileName);


//--------------------------------------------------------
// 以下、indexファイルから、「前のページ」と「後のページ」を作成する
function strip_html_comment($str)
{
    $str = preg_replace("/<\!--.*?-->/sm", '', $str); //comments
    return $str;
}

$explode_text = explode("\n", strip_html_comment($strMenuTemplate));

$explode_text_line_count = count($explode_text);
// 格納用の配列
$explode_array = array();
for ($i = 0; $i < $explode_text_line_count; $i++) {
    preg_match("/id=\"(nobu_.+?)\"/", $explode_text[$i], $explode_line_match);
    if ($explode_line_match) {
        if (array_key_exists($explode_line_match[1], $content_hash)) {
            if (count($explode_array) > 0) {
                // すでに要素があるなら、付けたし
                if ($explode_line_match[1] != $explode_array[0]) {
                    $content_hash[$explode_line_match[1]]["prev"] = $explode_array[0];
                }
            }

            array_unshift($explode_array, $explode_line_match[1]);
        }
    }
}

$current_page_prev = "";
$current_page_next = "";

$array_content_style = array();
$array_content_template = array();

foreach ($content_hash as $content_key => $content_value) {
    array_push($array_content_style, "%(" . $content_key . ")s");
    // 今のページと同じで、"prev" がある場合、
    if ($content_key == $urlParamPage && array_key_exists("prev", $content_value)) {
        $current_page_prev = $content_value["prev"];

        // 今表示しているページとは異なるキーが、prevとして今のページを指定しているということは、
        // そのキーは、現在表示しているページのnextである。 
    } else if (array_key_exists("prev", $content_value) && $content_value["prev"] == $urlParamPage) {
        $current_page_next = $content_key;
    }
}


if ($current_page_prev != "") {
    $current_page_prev = '<li class="page-item"><a class="page-link" href="?page=' . $current_page_prev . '"><i class="fa fa-caret-left fa-fw"></i>前へ</a></li>';
}

if ($current_page_next != "") {
    $current_page_next = '<li class="page-item"><a class="page-link" href="?page=' . $current_page_next . '">次へ<i class="fa fa-caret-right fa-fw"></i></a></li>';
}

if ($current_page_prev != "" || $current_page_next != "") {
    $footer_control_page = "\n" . '<div class="content-box mb-3 content-lighten"><ul class="pagination justify-content-center" style="margin:0px">%(prev)s %(next)s</ul></div>' . "\n";
    $strPageTemplate = $footer_control_page . $strPageTemplate . $footer_control_page;
}


// ページ内の上下に「前へ」と「次へ」を付け加える。
$array_style    = array(
    "%(prev)s",
    "%(next)s"
);
$array_template = array(
    $current_page_prev,
    $current_page_next
);
$strPageTemplate = str_replace($array_style, $array_template, $strPageTemplate);
// 以上、作成でした
//--------------------------------------------------------



// 左のメニューの部分。すでに開いているページに基いて、階層を開くところを決める。
// javascriptの一部を書き出す感じ
$strMenuExpand = "";
if ($orgParamPage != "" and $content_hash[$urlParamPage]['dir'] != "#") {
    $strMenuExpand = "$( '#menu' ).multilevelpushmenu( 'expand', '" . $content_hash[$urlParamPage]['dir'] . "' )";
}

// メインのスタイルシート
$timeStyleUpdate = filemtime("./style.min.css");
$strStyleUpdate = date("YmdHis", $timeStyleUpdate);

// 独自のFontAwesome
$timeFontPluginUpdate = filemtime("./font-awesome/css/font-awesome-plugin.css");
$strFontPluginUpdate = date("YmdHis", $timeFontPluginUpdate);

// メニューのカスタムCSS
$timeMLPMCSSUpdate = filemtime("./jquery/hc-offcanvas-nav.custom.css");
$strMLPMCSSUpdate = date("YmdHis", $timeMLPMCSSUpdate);

// メニューのカスタムJS
$timeMLPMCustomUpdate = filemtime("./jquery/hc-offcanvas-nav.custom.js");
$strMLPMCustomUpdate = date("YmdHis", $timeMLPMCustomUpdate);

// パンくずリストJS
$timeBreadCrumpUpdate = filemtime("jquery/hc-offcanvas-nav-breadcrump.js");
$strBreadCrumpUpdate = date("YmdHis", $timeBreadCrumpUpdate);


// 最終更新日時関連
$pageCTimeObj = filemtime($strPageFileFullPath);
if (isset($filetime) && $filetime > $pageCTimeObj) {
    $pageCTimeObj = $filetime;
}
$strPageDate = date('Y-m-d\TH:i:s', $pageCTimeObj);
$strCurrentYear = date("Y");
$strPageYMD = date('最終更新日 Y-m-d', $pageCTimeObj);
// デフォルトのページ
if ($urlParamPage == $defaultHomePage) {
    $strPageYMD = "";
}


// <h2>タグ内の文字列を取得する
preg_match('/<h2( .+?)?>(.*?)<\/h2>/is', $strPageTemplate, $matchesTitle);
if (isset($matchesTitle[2])) {
    $strH2Content = $matchesTitle[2];

    // タグと改行を削除
    $strH2ContentCleaned = strip_tags($strH2Content); // タグを削除
    $strH2ContentCleaned = preg_replace('/\s+/', ' ', $strH2ContentCleaned); // 改行を削除

    // ページ自体が属しているメニューカテゴリ名を取得
    $strParentDir = $content_hash[$urlParamPage]['dir'];

    // ページのH2要素内の文字列と、カテゴリ名の文字列が類似していれば、「ページのH2の中身」だけ
    // 全然違うようなら、「カテゴリ | ページのH2の中身」という構成にして妥当性を上げる
    $lvsDistance = levenshtein($strParentDir, $strH2ContentCleaned);
    $lvsSimilarity = 1 - $lvsDistance / max(strlen($strParentDir), strlen($strH2ContentCleaned));
    if ($lvsSimilarity > 0.7) {
        $strTitle = $strTitle . " | " . $strH2ContentCleaned;
    } else if (strlen($strParentDir) > 1) {
        $strTitle = $strTitle . " | " . $strParentDir . " | " . $strH2ContentCleaned;
    } else {
        $strTitle = $strTitle . " | " . $strH2ContentCleaned;
    }
}




// index内にある、スタイル、コンテンツ、階層の開きをそれぞれ、具体的な文字列へと置き換える
$array_style    = array(
    "%(style_dynamic)s",
    "%(title)s",
    "%(canonical)s",
    "%(description)s",
    "%(pagedate)s",
    "%(pageymd)s",
    "%(year)s",
    "%(menu)s",
    "%(expand)s",
    "%(styleupdate)s",
    "%(fontpluginupdate)s",
    "%(mlpmcssdate)s",
    "%(mlpmcustomdate)s",
    "%(shcore_head)s",
    "%(shcore_foot)s",
    "%(shcorecssupdate)s",
    "%(mathjax_foot)s",
    "%(breadcrump)s",
    "%(content_dynamic)s"
);
$array_template = array(
    $strStyleTemplate,
    $strTitle,
    $strCanonical,
    $strDescription,
    $strPageDate,
    $strPageYMD,
    $strCurrentYear,
    $strMenuTemplate,
    $strMenuExpand,
    $strStyleUpdate,
    $strFontPluginUpdate,
    $strMLPMCSSUpdate,
    $strMLPMCustomUpdate,
    $strShCoreHeader,
    $strShCoreFooter,
    $strShcoreCSSUpdate,
    $strMathJaxCoreFooter,
    $strBreadCrumpUpdate,
    $strPageTemplate
);
$strIndexEvaluated = str_replace($array_style, $array_template, $strIndexTemplate);

// トップページとして表示
echo ($strIndexEvaluated);