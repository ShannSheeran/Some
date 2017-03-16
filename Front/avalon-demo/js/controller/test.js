/**
 * Created by 3N on 2016/11/27.
 */
define(['js/jquery','js/mm/mmState', 'domReady!'], function($){
   var vmodels={
       test:avalon.define('test', function(vm){
           vm.data=[]
       }),
       detail:avalon.define({
           $id:'detail',
           id:0,
           aa1:[],
           getV:function(){

               $("input[name='aa[]']").each(function(index,item){
                       if ($(this).val() && vmodels.detail.aa1.length<3) {
                           vmodels.detail.aa1.push($(this).val());
                       } else {
                           alert('too long');
                           return false;
                       }
                   }
               );
           },
           getV2:function(){
               var $data = [{
                   path:'Uploads/images/e77eeffrfr77frrrr6f/',
                   file_name:'2.png'
               },{
                   path:'Uploads/images/e77eeffrfr77fr6f/',
                   file_name:'3.png'
               }];
               var hh={data:$data};
               $.ajax({
                   url:'http://www.me.com/avalon-demo/index.php',
                   method:'GET',
                   data:hh,
                   success:function(d){
                        console.log(d);
                   }
               })
           }
       }),

       net:avalon.define('net', function(vm){
           vm.compromise=''
       }),
   }

    $.ajax({
        url:"http://www.3n.com/api.php/projects",
        method:"GET",
        success:function(res) {
            vmodels.test.data=res.data.data_list;
        }
    });
   return vmodels;
});