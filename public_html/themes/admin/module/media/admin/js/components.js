const fileItem = {
    template:'#file-item-template',
    data: function () {
        return {
            count: 0
        }
    },
    props:['file',"selected","viewType"],
    methods:{
        selectFile(file){
            this.$emit('select-file',file);
        },
        onDragStart(event){
            if(!this.file || !this.file.id){
                return;
            }

            event.stopPropagation();

            let fileIds = [this.file.id];
            if(this.selected.indexOf(this.file.id) !== -1){
                fileIds = this.selected.slice();
            }

            event.dataTransfer.setData('application/json', JSON.stringify({
                type: 'files',
                ids: fileIds
            }));
            event.dataTransfer.effectAllowed = 'move';
        },
        fileClass(file){
            var s = [];
            s.push(file.file_type);

            if(file.file_type.substr(0,5)=='image'){
                s.push('is-image');
            }else{
                s.push('not-image');
            }
            return s;
        },
        getFileThumb(file){
            if(file.file_type.substr(0,5)=='image'){
                return '<img src="'+file.thumb_size+'">';
            }
            if(file.file_type.substr(0,5)=='video'){
                return '<img src="/icon/007-video-file.png">';
            }
            if(file.file_type.indexOf('x-zip-compressed')!== -1 || file.file_type.indexOf('/zip')!== -1){
                return '<img src="/icon/005-zip-2.png">';
            }
            if(file.file_type.indexOf('/pdf')!== -1 ){
                return '<img src="/icon/002-pdf-file-format-symbol.png">';
            }

            if(file.file_type.indexOf('/msword')!== -1 || file.file_type.indexOf('wordprocessingml')!== -1){
                return '<img src="/icon/010-word.png">';
            }
            if(file.file_type.indexOf('spreadsheetml')!== -1  || file.file_type.indexOf('excel')!== -1){
                return '<img src="/icon/011-excel-file.png">';
            }
            if(file.file_type.indexOf('presentation')!== -1 ){
                return '<img src="/icon/powerpoint.png">';
            }
            if(file.file_type.indexOf('audio/')!== -1 ){
                return '<img src="/icon/006-audio-file.png">';
            }

            return '<img src="/icon/008-file.png">';

        },
        humanFileSize:function (bytes, si=false, dp=1) {
            if(typeof bytes == 'undefined' || !bytes) return '';
            const thresh = si ? 1000 : 1024;

            if (Math.abs(bytes) < thresh) {
                return bytes + ' B';
            }

            const units = si
                ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
                : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
            let u = -1;
            const r = 10**dp;

            do {
                bytes /= thresh;
                ++u;
            } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);


            return bytes.toFixed(dp) + ' ' + units[u];
        }
    }
}

const folderItem = {
    template:'#folder-item-template',
    data: function () {
        return {
            folder_name: '',
            saving:false,
            isDragOver:false
        }
    },
    props:{
        folder:{
            type:Object,
            default:{
                name:'',
                id:'',
                onEdit:false
            }
        },
        index:{
            type:Number,
            default:0
        },
        viewType:{
            type:String,
            default:'grid'
        }
    },
    watch:{
        folder:function (val){
            var me = this;
            if(val.onEdit){
                this.$nextTick(function (){
                    if(me.$refs.input){
                        me.$refs.input.select();
                    }
                })
            }
        }
    },
    methods:{
        deleteFolder:function (event){
          if(event){
              event.preventDefault();
              event.stopPropagation();
          }

          if(!this.folder.id){
              this.$emit('deleted', this.index);
              return;
          }

          if(this.saving) return;

          var me = this;
          bookingCoreApp.showConfirm({
              message: i18n.confirm_delete_folder,
              callback: function(result){
                  if(!result) return;
                  if(me.saving) return;

                  me.saving = true;
                  $.ajax({
                      url:bookingCore.url+'/media/folder/delete',
                      data:{
                          id:me.folder.id,
                      },
                      type:'post',
                      dataType:'json',
                      success:function (json){
                          me.saving = false;
                          if(json.status){
                              me.$emit('deleted',me.index);
                              bookingCoreApp.showAjaxMessage(json)
                          }else{
                              bookingCoreApp.showAjaxMessage(json)
                          }
                      },
                      error:function (e){
                          me.saving = false;
                          bookingCoreApp.showAjaxError(e)
                      }
                  })
              }
          });
        },
        openEdit:function (event){
            if(event){
                event.preventDefault();
                event.stopPropagation();
            }
            this.$emit('toggle-edit',this.index,true);
        },
        saveName:function(){
            if(this.saving) return;

            var me = this;
            this.saving = true;
            $.ajax({
                url:bookingCore.url+'/media/folder/store',
                data:{
                    id:this.folder.id,
                    name:this.folder_name,
                    parent_id:this.folder.parent_id
                },
                type:'post',
                dataType:'json',
                success:function (json){
                    me.saving = false;
                    if(json.status){
                        me.$emit('update',me.index,json.data);
                    }
                },
                error:function (e){
                    me.saving = false;
                    bookingCoreApp.showAjaxError(e)
                }
            })
        },
        onDragStart:function(event){
            if(!this.folder.id){
                event.preventDefault();
                return;
            }

            event.stopPropagation();
            event.dataTransfer.setData('application/json', JSON.stringify({
                type: 'folder',
                id: this.folder.id
            }));
            event.dataTransfer.effectAllowed = 'move';
        },
        onDragOver:function(event){
            if(!this.folder.id){
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            this.isDragOver = true;
            if(event.dataTransfer){
                event.dataTransfer.dropEffect = 'move';
            }
        },
        onDragLeave:function(){
            this.isDragOver = false;
        },
        onDrop:function(event){
            this.isDragOver = false;
            if(!this.folder.id){
                return;
            }

            this.$emit('media-drop', this.folder.id, event);
        }
    },
    mounted() {
        var me = this;
        this.folder_name = this.folder.name;
        if(this.folder.onEdit && this.$refs.input){
            this.$refs.input.select();
        }
    }
}
export { fileItem, folderItem}
