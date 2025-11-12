<div class="panel" id="VisaFormBuider" v-cloak>
    <div class="panel-title"><strong>{{__("Form Builder")}}</strong></div>
    <div class="panel-body">
        <form-node v-bind:node="items[nodeId]" v-bind:items="items" v-for="nodeId in items.ROOT.nodes" v-bind:key="nodeId"></form-node>
        <div class="d-flex justify-content-end">
            <a class="btn btn-sm btn-default" v-on:click.prevent="addNode('ROOT')">{{__("Add field")}}</a>
        </div>
    </div>
</div>
<template id="form-node-template">
    <div class="form-node">
        <div class="d-flex justify-content-between mb-3">
            <div>
                <i v-bind:class="typeObject.icon" class="mr-2"></i>
                <span v-text="node.label || typeObject.label" class="link"></span> 
            </div>
            <div>
                <select class="form-control" v-on:change="node.type = $event.target.value">
                    <option v-bind:value="type.id" v-bind:key="type.id" v-for="type in types" v-text="type.label"></option>
                </select>
            </div>
        </div>
        <template v-if="node.type === 'section'">
            <div class="form-node type-section">
                <form-node v-bind:node="node" v-bind:items="items" v-for="nodeId in node.nodes" v-bind:key="nodeId"></form-node>
                <div class="d-flex justify-content-end">
                    <a class="btn btn-sm btn-default" v-on:click.prevent="addNode">{{__("Add field")}}</a>
                </div>
            </div>
        </template>
    </div>
</template>