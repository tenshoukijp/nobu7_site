curl -s https://xn--petw8uc11b.jp/sitemap.php
timeout /t 5

cmd /C C:/usr/nextftp/NEXTFTP_CLI_NO_FOCUS.EXE $Host13 -local="G:/repogitory_jp/nobu7_site" -quit2 -nosound -minimize -download=sitemap.xml -nokakunin

