var $,tab,dataStr,layer;
layui.config({
	base : "/static/client/js/"
}).extend({
	"bodyTab" : "bodyTab"
})
layui.use(['layer','jquery'],function(){
	var $ = layui.$;
    	layer = parent.layer === undefined ? layui.layer : top.layer;
})

