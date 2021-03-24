getall_list();
            var nsm_list =  "";
            var asm_list = "";
            var distributor_list = "";
            var dsr_list = "";
            function getall_list(){
                NProgress.start();
                $.ajax({
                    type: 'POST',
                    url: "http://localhost:8090/Common_function/all_list/",
                    dataType: "json",
                    data: '',
                    beforeSend: function () {

                    },
                    success: function (data) {
                        nsm_list = data.nsm;
                        asm_list = nsm_list[0].asm;
                        distributor_list = asm_list[0].distributor;
                        dsr_list = distributor_list[0].dsr;
                        nsm77_lst(nsm_list);
                        asm785list(asm_list);
                        di5464jkhjkcd(distributor_list);
                        dsr995(dsr_list);
                    }
                });
                NProgress.done();
        }
        /*NSM Listing*/
        function nsm77_lst(nsm_list){
            var htm = "";
            nsm_list.forEach(function(entry) {
                htm+= "<option value='"+entry.nsm_id+"'>"+entry.nsm_code+"-"+entry.nsm_name+"</option>";
            })
            $('#nsm').html(htm);
        }
        /*ASM Listing*/
        function n855_list(obj){
            
            asm_list = new a885Key(nsm_list,obj.value);
            asm785list(asm_list);
            
            /*reinitiating Distributor from 0*/
            distributor_list =new a5464jkhjkKey(asm_list,asm_list[0].asm_id);
            di5464jkhjkcd(distributor_list);
            /*reinitiating DSR from 0*/
            dsr_list =new a995Key(distributor_list,distributor_list[0].distributor_id);
            dsr995(dsr_list);
        }
        
        a885Key = function(nsm,value){
            var toreturn;
            nsm.forEach(function(entry) {                
                if(entry.nsm_id == value){
                toreturn = entry.asm;            
            return false;
                }
            })
            return toreturn;
        };
        
        function asm785list(asm_list){
            var htm = "<option value='0'>All</option>";
            var sel = true;            
            asm_list.forEach(function(entry) {  
                htm+= "<option value='"+entry.asm_id+"' "+(sel ? " selected ":"")+" >"+entry.asm_code+"-"+entry.asm_name+"</option>";
                sel = false;
            })
            $('#asm').html(htm);
            /*also  change distributor*/            
        }
        /*distributor Listing*/
        function dis778ibutor(obj){  
            if(obj.value ==0){
                /*set all  for readonly*/
                $('#distributor').html("<option value=0>All</option>");
                $('#distributor').prop('disabled', true);
                
                $('#dsr').html("<option value=0>All</option>");
                $('#dsr').prop('disabled', true);
            }else{
                $('#distributor').prop('disabled', false);
                $('#dsr').prop('disabled', false);
            }
            distributor_list =new a5464jkhjkKey(asm_list,obj.value);
            di5464jkhjkcd(distributor_list);
            /*reinitiating dsr from list 0*/            
            dsr_list = new a995Key(distributor_list,distributor_list[0].distributor_id);
            dsr995(dsr_list);
        }
        a5464jkhjkKey = function(asm_list,value){
            var toreturn;
            asm_list.forEach(function(entry) {                
                if(entry.asm_id == value){
                toreturn = entry.distributor;            
            return false;
                }
            })
            return toreturn;
        }

        function di5464jkhjkcd(dis_list){
            //distributor list
            var htm = "<option value='0'>All</option>";
            var sel=true;        
            dis_list.forEach(function(entry) {                 
                htm+= "<option value='"+entry.distributor_id+"' "+(sel ? " selected ":"")+" >"+entry.distributor_code+"-"+entry.distributor_name+"</option>";
                sel = false;  
            })
            $('#distributor').html(htm);
        }
        /*dsr listing*/
        function dsr896(obj){
            if(obj.value ==0){
                /*set all  for readonly*/                
                $('#dsr').html("<option value='0'>All</option>");
                $('#dsr').prop('disabled', true);
            }else{
                $('#dsr').prop('disabled', false);
            }
            dsr_list =new a995Key(distributor_list,obj.value);
            dsr995(dsr_list);
        }
        asm_list = function(distributor_list,value){
            var toreturn;
            distributor_list.forEach(function(entry) {                
                if(entry.distributor_id == value){
                toreturn = entry.dsr;            
            return false;
                }
            })
            return toreturn;
        }
        a995Key = function(distributor_list,value){
            var toreturn;
            distributor_list.forEach(function(entry) {                
                if(entry.distributor_id == value){
                toreturn = entry.dsr;            
            return false;
                }
            })
            return toreturn;
        }
        function dsr995(dsr_list){
            var htm = "<option value='0'>All</option>";
            var sel=true;
            dsr_list.forEach(function(entry) {                
                htm+= "<option value='"+entry.dsr_id+"' "+(sel ? " selected ":"")+">"+entry.dsr_code+"-"+entry.dsr_name+"</option>";
                sel = false;
            })
            $('#dsr').html(htm);
        }
        