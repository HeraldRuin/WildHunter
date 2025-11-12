<template>
	<div class="layer-item" :class="{selected:selectedBlockId === id}">
		<div class=" layer-name d-flex justify-content-between align-items-center b-py-0" :class="{'b-pl-2': parent }">
			<!--       Show Arrow icon if there are children or block is container type-->
			<svg
				@click="showChildren = !showChildren"
				class="b-cursor-pointer b-mr-2 b-w-4 b-h-4"
				v-if="block.nodes || block.is_container"
				xmlns="http://www.w3.org/2000/svg"
				fill="none"
				viewBox="0 0 24 24"
				stroke-width="1.5"
				stroke="currentColor"
			>
				<path v-if="!showChildren" stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
				<path v-if="showChildren" stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
			</svg>
			<div class="drag-handler flex-grow-1 b-truncate b-text-sm" @click.prevent="selectBlock">
				{{ block.name }}
				<strong class="b-ml-2" v-if="block.type === 'column'">{{ block.model['size'] }}/12</strong>
			</div>
			<div class="dropdown" title="Options">
				<span data-toggle="dropdown" class="py-1 px-2">
					<i class="fa fa-cog"></i>
				</span>
				<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
					<a class="dropdown-item text-danger" @click="deleteItem" href="#">Delete</a>
				</div>
			</div>
		</div>
		<div class="layer-children b-pl-6" v-show="showChildren">
			<layer-item
				v-if="block.nodes"
				@select-block="emmitSelect"
				:selected-block-id="selectedBlockId"
				:items="items"
				v-for="(childId, index) in block.nodes"
				:key="index"
				:id="childId"
				:parent="id"
			></layer-item>
			<div
				v-if="block.is_container"
				@click.prevent="showAddBlock"
				class="b-px-2 b-py-1 b-cursor-pointer b-flex-inline b-items-center"
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					fill="none"
					viewBox="0 0 24 24"
					stroke-width="1.5"
					stroke="currentColor"
					class="b-w-4 b-h-4"
				>
					<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
				</svg>
				<span v-if="block.type == 'row'">{{ i18n.add_column }}</span>
				<span v-else>{{ i18n.add_children }}</span>
			</div>
		</div>
	</div>
</template>

<script>

export default {
  name: 'layer-item',
  data: function () {
    return {
      i18n: template_i18n,
      showChildren: false
    }
  },
  props: {
    items: {},
    id: '',
    selectedBlockId: '',
    parent: ''
  },
  computed: {
    block() {
      return this.items[this.id] ?? {}
    }
  },
  methods: {
    selectBlock() {
		window.LiveEditorEventBus.emit('select-block', this.id);
    },
    emmitSelect(id) {
		window.LiveEditorEventBus.emit('select-block', id);
    },
    sortEnd(val) {
      console.log(val)
    },
    deleteItem() {
      if (!confirm(this.i18n.delete_confirm)) return;

      window.LiveEditorEventBus.emit('delete-item', {
        id: this.id
      })
    },
    showAddBlock() {
      // if current block is row, add a sample column to it
      if (this.block.type === 'row') {
        window.LiveEditorEventBus.emit('add-block', {
          to: this.id,
          children: [{
            id: 'column',
            model: {
              size: 6 //  Default is 6/12
            },
            parent: this.id
          }]
        })
      } else {
        window.LiveEditorEventBus.emit('show-add-block', {
          id: this.id,
        })
      }
    }
  }
}
</script>
