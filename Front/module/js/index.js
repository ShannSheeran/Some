/**
 * Created by 3N on 2016/11/25.
 */
require(['./js/vmodels', './lib/mm/mmState'], function(vmodels){
    avalon.state('/', {
        url:'/',
        template:"<p style='width: 100%;height: 100%;font-size: 50px;'>Hello Avalon!</p>"
    });
    avalon.state('home',{
        url:"/home",
        views:{
            "":{
                templateUrl:"./page/aa.html",
            }
        }
    });

    avalon.state('bb', {
        url:"/bb",
        onEnter:function(){
            $.ajax({
                url:'http://www.3n.com/api.php/users',
                method:'GET',
                success:function(r){
                    vmodels.bat.data= r.data.data_list;
                }
            });
        },
        views:{
            "":{
                templateUrl:'./page/bb.html',
            }
        }
    });

    avalon.state('cc', {
        url:'/cc/:id/:type',
        onEnter: function() {
            vmodels.mytest.id=this.params.id;
            vmodels.mytest.type=this.params.type;
            vmodels.mytest.fg='dddddd';
        },
        views:{
            "":{
                templateUrl:"./page/cc.html"
            }
        }
    });

    avalon.state('list', {
        url:"/list",
        onEnter:function(){
            var current_page=1;
            var page_size=5;
            var vm = avalon.define({
                $id:'ch',
                name:'你好',
                current_page:current_page,
                data:[],
                count:0,
                next:function(){
                    if (vm.data.length>=page_size){
                        current_page +=1;
                    }

                    vm.current_page=current_page;

                    jQuery.ajax({
                        url:'http://www.3n.com/api.php/users?page_no='+current_page+"&page_size="+page_size,
                        method:'GET',
                        success:function(d){
                            vm.data= d.data.data_list;
                            vm.count= parseInt(d.data.count/page_size);
                            if(d.data.data_list.length<page_size)
                            {
                                layer.alert('已经是最后一页啦');
                            }
                            //console.log(d.data.data_list);
                        }
                    });
                }
            });
            jQuery.ajax({
                    url:'http://www.3n.com/api.php/users?page_no='+current_page+"&page_size="+page_size,
                    method:'GET',
                    success:function(d){
                        vm.data= d.data.data_list;
                        vm.count= parseInt(d.data.count/page_size);
                    }
            });
            avalon.filters.dates = function(str){
                return new Date(parseInt(str) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
            }
        },
        views:{
            "":{
                templateUrl:'./page/ff.html',
            }
        }
    });

    avalon.state('detail',{
        url:'/detail/:id',
        onEnter:function(){
            if (this.params.id) {
                vmodels.detail.id=this.params.id;
            }
        },
        views:{
            "":{
                templateUrl:'./page/detail.html',
            }
        }
    });


    avalon.history.start({
        basepath: "./lib/mm"

    })

    avalon.scan();

});