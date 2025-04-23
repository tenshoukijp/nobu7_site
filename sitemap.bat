curl -s https://xn--petw8uc11b.jp/sitemap.php
timeout /t 5

cmd /C C:/usr/nextftp/NEXTFTP.EXE $Host13 -local="G:/repogitory_jp/nobu7_site" -quit -nosound -minimize -download=sitemap.xml -nokakunin

