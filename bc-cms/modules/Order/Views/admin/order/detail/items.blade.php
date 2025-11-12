<div class="form-group-item bg-white">
    <div class="g-items-header">
        <div class="row">
            <div class="col-md-6">{{__("Product")}}</div>
            <div class="col-md-1">{{__("Qty")}}</div>
            <div class="col-md-2">{{__("Price")}}</div>
            <div class="col-md-2">{{__("Total")}}</div>
            <div class="col-md-1"></div>
        </div>
    </div>
    <div class="g-items">
        <bc-order-item v-for="(item,index) in items" :key="index" :index="index" :item="item" @del="delItem" @change="changeItem" :is_editable="is_editable"></bc-order-item>
        <div class="item" style="background: #f7f7f7;">
            <div class="row">
                <div class="col-md-6">

                </div>
                <div class="col-md-6">
                    <div class="d-flex mb-2">
                        <div class="col-8 text-right ">{{__("Subtotal")}}</div>
                        <div class="col-4 text-right font-weight-bold">@{{ formatMoney(_subtotal) }}</div>
                    </div>
                    <div class="d-flex mb-2 align-items-center">
                        <div class="col-8 text-right ">
                            <div class="form-inline justify-content-end">
                                <label class="mr-2">{{__("Shipping")}}</label>
                                <select v-if="is_editable" class="form-control" v-model="shipping_method">
                                    <optgroup label="{{__("Shipping Method")}}">
                                        <option value="">{{__("N/A")}}</option>
                                        <option v-for="(m,key) in shipping_methods" :value="key">@{{m.name}}</option>
                                        <option value="other">{{__("Other")}}</option>
                                    </optgroup>
                                </select>
                                <span v-else="">@{{ shipping_method }}</span>
                            </div>
                        </div>
                        <div class="col-4 text-right font-weight-bold">
                            <input v-if="is_editable" type="number" class="form-control text-right" v-model.number="shipping_amount">
                            <span v-else="">@{{ shipping_amount }}</span>
                        </div>
                    </div>
                    @if(\Modules\Product\Models\TaxRate::isEnable())
                    <div class="d-flex mb-2 align-items-center">
                        <div class="col-8 text-right ">
                            <div class="form-inline justify-content-end">
                                {{__("Tax")}} ({{\Modules\Product\Models\TaxRate::isPriceInclude() ? __("Include") : __("Exclude")}})
                            </div>
                        </div>
                        <div class="col-4 text-right font-weight-bold">@{{ formatMoney(_tax_amount) }}</div>
                    </div>
                    <div class="d-flex mb-2 align-items-center">
                        <div class="col-8 text-right ">
                            <div class="form-inline justify-content-end">
                                <div>
                                    <div v-for="(tax,index) in tax_lists">
                                        <input type="checkbox" v-model="tax_lists[index]['active']" :value="index"> @{{ tax.name }} @{{tax.tax_rate}}%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 text-right font-weight-bold"></div>
                    </div>
                    @endif
                    <div class="d-flex">
                        <div class="col-8 text-right ">{{__("Grand total")}}</div>
                        <div class="col-4 text-right font-weight-bold">@{{ formatMoney(_total) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="item" style="background: #f7f7f7;">
            <div class="row">
                <div class="col-md-12 text-right">
                    <span v-if="is_editable" class="btn btn-info btn-sm " @click="addItem"><i class="icon ion-ios-add-circle-outline"></i> {{__("Add item")}}</span>
                    <span v-else class="alert text-danger">{{__("You need to change orders status to On-Hold to edit order items")}}</span>
                </div>
            </div>
        </div>

    </div>

</div>
