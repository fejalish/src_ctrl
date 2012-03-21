//(function() {
	var img=document.createElement('img');
	img.setAttribute('style','width:1px;height:1px;display:none');
	img.setAttribute('src','http://src.fejalish.com/screen.php?cw='+document.documentElement.clientWidth);
//})();
function src_ctrl_update(){
	img.setAttribute('src','http://src.fejalish.com/screen.php?cw='+document.documentElement.clientWidth);
}
window.onresize = function(event) {
	src_ctrl_update();
}
window.onload = function () {
	src_ctrl_update();
}
