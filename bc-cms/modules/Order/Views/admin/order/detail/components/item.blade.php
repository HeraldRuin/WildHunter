<script type="text/x-template" id="bc-order-item-template">
    <div class="item" data-number="1">
        <div class="row">
            <div class="col-md-4">
                <bc-select2 v-if="is_editable" placeholder="{{__('-- Select Product --')}}" :settings="product_settings" :options="product_options" v-model="item.product_id" @select="productChangeEvent"/>
                <span v-else="">@{{product.title}}</span>
            </div>
            <div class="col-md-2">
                <select class="form-control" v-show="product.product_type == 'variable'" v-model="item.variation_id" @change="variationChange">
                    <option value="">{{__("-- Select Variation --")}}</option>
                    <option v-for="v in variations" :value="v.id">@{{ v.name }}</option>
                </select>
            </div>
            <div class="col-md-1">
                <input v-if="is_editable" type="number" min="1" :max="remain_stock" step="1" class="form-control" v-model="item.qty">
                <span v-else="">@{{ item.qty }}</span>
            </div>
            <div class="col-md-2 text-right">
                @{{ formatMoney(item.price) }}
            </div>
            <div class="col-md-2 text-right">
                @{{ formatMoney(subtotal) }}
            </div>
            <div class="col-md-1">
                <span v-if="is_editable" class="btn btn-danger btn-sm" @click="del"><i class="fa fa-trash"></i></span>
            </div>
        </div>
    </div>
</script>

<script type="text/javascript">
    Vue.component('bc-order-item', {
        template:'#bc-order-item-template',
        data() {
            return {
                select2: null,
                type:'billing',
                countries:[],
                active:0,
                product_settings:{
                    width:'100%',
                    allowClear  :true,
                    ajax:{
                        'url' : BC.routes.product.getForSelect2+'?needs[]=price',
                        'dataType': 'json',
                        processResults: function (data) {
                            // Transforms the top-level key of the response object from 'items' to 'results'
                            return {
                                results: data.data
                            };
                        }
                    }
                },
                product_options:[
                ],
                product:{
                    type:''
                },
                variations:[],
                remain_stock : 0
            };
        },
        props: {
            index:{
                type:Number,
                default:''
            },
            item:{
                type:Object,
                default:{
                    id:'',
                    price:0,
                    qty:1,
                    variation_id:'',
                    product_id:0
                }
            },
            is_editable:true
        },
        watch: {
        },
        computed:{
            subtotal:function(){
                return this.item.qty * this.item.price;
            }
        },
        methods: {
            formatMoney:function(f){
                return bc_format_money(f)
            },
            del:function(){
                this.$emit('del',this.index)
            },
            save:function(){
                console.log(this.index,Object.assign({},this.item))
                this.$emit('change',this.index,Object.assign({},this.item));
            },
            show(type,fields){
                $('#modal-address').modal('show');
                this.type = type;
                this.fields = fields;
            },
            hide(){
                $('#modal-address').modal('hide');
            },
            productChange:function (data) {
                this.product = data;
                this.item.product_id = data.id;
                this.variations = this.product.variations ?? [];
                if(data.product_type == 'simple'){
                    this.item.price = data.price;
                    this.remain_stock = data.remain_stock;
                    if(!data.is_manage_stock && data.stock_status == 'in'){
                        this.remain_stock = null;
                    }
                }else if(data.product_type == 'variable'){
                    let variation_id = this.item.variation_id;
                    var find = this.variations.find(function(item){
                        return item.id == variation_id;
                    })
                    if(find){
                        this.item.price = find.price;
                        this.remain_stock = find.remain_stock;
                        if(!find.is_manage_stock && find.stock_status == 'in'){
                            this.remain_stock = null;
                        }
                    }
                } else{
                    this.item.price = 0;
                    this.remain_stock = 0;
                }
            },
            productChangeEvent:function(data){
                this.productChange(data)
                this.save();
            },
            variationChange:function (e) {
                let variation_id = e.target.value;
                var find = this.variations.find(function(item){
                    return item.id == variation_id;
                })
                if(find){
                    this.item.price = find.price;
                    this.remain_stock = find.remain_stock;
                    if(!find.is_manage_stock && find.stock_status == 'in'){
                        this.remain_stock = null;
                    }
                }
            },
            variationChangeEvent:function (e) {
                this.variationChange(e.target.value);
                this.save();
            },
        },
        mounted() {
            var me = this;
            if(this.item.product_id){
                this.product_options.push({
                    id:this.item.product_id,
                    text:this.item.title
                })
                this.productChange(this.item.product)
                if(typeof this.item.product !='undefined' && typeof this.item.product.product_type == 'variable'){
                    this.variations = this.item.product.variations;
                    this.variationChange(this.item.variation_id);
                }
            }
        },
        beforeDestroy() {
        }
    });
</script>
