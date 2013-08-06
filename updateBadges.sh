cd www/zalbee/kong
echo "badges = " > badges.tmp
wget -o wget.log -O - http://www.kongregate.com/badges.json >> badges.tmp && mv badges.tmp badges.js
