cd /d %~dp0

cmd /C C:/usr/nextftp/NEXTFTP_CLI.EXE $Host13 -local="G:/repogitory_jp/nobu7_site" -dir="public_html/xn--petw8uc11b.jp/thumbnail" -quit -nosound -minimize -upload=%1 -nokakunin
