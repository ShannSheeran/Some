/**
 * Created by 3N on 2016/11/25.
 */

require(['js/controller/test', 'js/mm/mmState'], function(vmodels){
    avalon.state('/', {
        url:'/',
        template:"<p style='width: 100%;height: 100%;font-size: 50px;'>Hello Avalon!</p>"
    });

    avalon.state('home', {
        url:"/home",
        views:{
            "":{
                templateUrl:'tpl/test.html'
            }
        }
    });

    avalon.state('detail', {
        url:'/detail/:id',
        onEnter:function(){
            var id = this.params.id;
            if (id) {
                vmodels.detail.id=id;
            }
        },
        views:{
            "main":{
                templateUrl:'tpl/detail.html'
            },
            "net":{
                templateUrl:'tpl/net.html'
            }
        }
    });

    avalon.history.start({
        basepath: "lib/mm"
    })

    avalon.scan();

});