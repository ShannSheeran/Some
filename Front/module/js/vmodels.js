/**
 * Created by 3N on 2016/11/25.
 */
define(['../lib/mm/mmState', 'domReady!'], function(){
    var vmodels={
        page:avalon.define({
            $id:"test",
            test:"ASC"
        }),
        bat:avalon.define({
            $id:'bat',
            id:'',
            data:[],
            click:function(){
                layer.confirm('您是如何看待前端开发？', {
                    btn: ['重要','奇葩'] //按钮
                }, function(){
                    layer.msg('的确很重要', {icon: 1});
                }, function(){
                    layer.msg('也可以这样', {
                        time: 20000, //20s后自动关闭
                        btn: ['明白了', '知道了']
                    });
                });
            },
            title:'Ed Sheeran'
        }),
        mytest:avalon.define({
            $id:"test1",
            id:'',
            type:'',
            fg:'ctl'
        }),
        detail:avalon.define({
            $id:'detail',
            id:'',
        }),
    }
    return vmodels;
});