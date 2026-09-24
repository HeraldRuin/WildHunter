<div class="blog-editor-root list-animal-width custom-fluid">
<div id="blog-editor" class="flex-column overflow-auto" v-cloak>
    <div class="blog-topbar flex-shrink-0 d-flex justify-content-between align-items-center py-3 px-3">
        <div class="d-flex align-items-center">
            <a href="{{ $index_route }}" class="px-3 lh-26 text-26 text-black border-right-1 border-right-solid border-right-gray mr-3">
                <i class="ion ion-ios-close-circle-outline"></i>
            </a>
            <span class="blog-title-input">{{ $row->title ?: __('Untitled') }}</span>
        </div>
        <div class="d-flex align-items-center">
            <span
                class="alert-text mr-3"
                v-show="message.content"
                :class="message.type ? 'text-success' : 'text-danger'"
            >@{{ message.content }}</span>
            <span class="last_saved font-italic mr-3" v-if="lastSaved">{{ __('Last saved:') }} @{{ lastSaved }}</span>
            <button class="btn btn-primary" @click="save" :disabled="saving">
                <i class="fa fa-save"></i> {{ __('Save') }}
            </button>
        </div>
    </div>

    <div class="blog-editor-workspace d-flex flex-grow-1 position-relative overflow-hidden">
        <div class="blog-left-zone">
            <div class="blog-zone-header">
                <h5 class="mb-0">{{ __('Sections') }}</h5>
            </div>
            <div class="blog-blocks-list overflow-auto">
                <draggable v-model="blocks" item-key="id" handle=".drag-handler" @change="onSort">
                    <template #item="{ element, index }">
                        <div>
                            <div
                                class="blog-block-item"
                                :class="{ selected: selectedBlockId === element.id }"
                                @click="selectBlock(element.id)"
                            >
                                <span class="drag-handler"><i class="fa fa-bars"></i></span>
                                <span class="block-icon"><i :class="blockIcon(element.type)"></i></span>
                                <span class="block-label">@{{ blockLabel(element) }}</span>
                                <button class="btn btn-sm btn-link text-danger block-delete" @click.stop="deleteBlock(index)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                            <div v-if="element.type === 'columns'" class="blog-block-children">
                                <template v-for="(col, ci) in element.columns" :key="col.id">
                                    <div class="blog-block-col-label">
                                        <span>{{ __('Column') }} @{{ ci + 1 }}</span>
                                        <button class="btn btn-sm btn-link text-danger block-delete" @click.stop="deleteColumn(element, ci)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <div
                                        v-for="child in col.blocks"
                                        :key="child.id"
                                        class="blog-block-item blog-block-item--nested"
                                        :class="{ selected: selectedBlockId === child.id }"
                                        @click="selectBlock(child.id)"
                                    >
                                        <span class="block-icon"><i :class="blockIcon(child.type)"></i></span>
                                        <span class="block-label">@{{ blockLabel(child) }}</span>
                                        <button class="btn btn-sm btn-link text-danger block-delete" @click.stop="deleteNestedBlock(element, ci, child.id)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </draggable>
                <div v-if="!blocks.length" class="text-muted text-center p-3">{{ __('No blocks yet') }}</div>
            </div>
            <div class="blog-add-blocks p-2 border-top">
                <p class="small text-muted mb-2">{{ __('Add block') }}</p>
                <button class="btn btn-sm btn-outline-secondary btn-block mb-1" @click="addBlock('text')">
                    <i class="fa fa-font"></i> {{ __('Text') }}
                </button>
                <button class="btn btn-sm btn-outline-secondary btn-block mb-1" @click="addBlock('image')">
                    <i class="fa fa-image"></i> {{ __('Image') }}
                </button>
                <button class="btn btn-sm btn-outline-secondary btn-block mb-1" @click="addBlock('table')">
                    <i class="fa fa-table"></i> {{ __('Table') }}
                </button>
                <button class="btn btn-sm btn-outline-secondary btn-block" @click="addBlock('columns')">
                    <i class="fa fa-columns"></i> {{ __('Columns layout') }}
                </button>
            </div>
        </div>

        <div class="blog-editor-main d-flex flex-column flex-grow-1 min-width-0">
        <div class="blog-content-zone overflow-auto flex-grow-1" @click.self="selectedBlockId = null">
            <div class="blog-preview-container">
                <div
                    v-for="(block, index) in blocks"
                    :key="block.id"
                    class="blog-preview-block"
                    :class="{ selected: selectedBlockId === block.id }"
                    @click.stop="selectBlock(block.id)"
                >
                    <div class="blog-preview-block-toolbar">
                        <span>@{{ blockLabel(block) }}</span>
                        <button class="btn btn-sm btn-link" @click.stop="insertBlockAfter(index)">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                    <div v-if="block.type === 'text'" class="blog-preview-text" v-html="block.content || '<p>{{ __('Empty text block') }}</p>'"></div>
                    <div v-else-if="block.type === 'image'" class="blog-preview-image" :style="{ textAlign: block.settings?.align || 'center' }">
                        <img v-if="block.image_url" :src="block.image_url" :alt="block.caption || ''" :style="{ maxWidth: block.settings?.width || '100%' }">
                        <div v-else class="blog-preview-image-placeholder">
                            <i class="fa fa-image fa-3x"></i>
                            <p>{{ __('Select an image') }}</p>
                        </div>
                        <p v-if="block.caption" class="blog-image-caption">@{{ block.caption }}</p>
                    </div>
                    <div v-else-if="block.type === 'table'" class="blog-preview-table">
                        <table class="table table-bordered">
                            <thead v-if="block.settings?.headerRow && block.rows.length">
                                <tr>
                                    <th v-for="(cell, ci) in block.rows[0]" :key="ci" v-html="cell || '&nbsp;'"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, ri) in (block.settings?.headerRow ? block.rows.slice(1) : block.rows)" :key="ri">
                                    <td v-for="(cell, ci) in row" :key="ci" v-html="cell || '&nbsp;'"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else-if="block.type === 'columns'"
                        class="blog-columns"
                        :data-ratio="block.settings?.ratio || '50-50'"
                    >
                        <div
                            v-for="(col, ci) in block.columns"
                            :key="col.id"
                            class="blog-columns-col"
                        >
                            <div
                                v-for="(child, cidx) in col.blocks"
                                :key="child.id"
                                class="blog-preview-block blog-preview-block--nested"
                                :class="{ selected: selectedBlockId === child.id }"
                                @click.stop="selectBlock(child.id)"
                            >
                                <div v-if="child.type === 'text'" class="blog-preview-text" v-html="child.content || '<p>{{ __('Empty text block') }}</p>'"></div>
                                <div v-else-if="child.type === 'image'" class="blog-preview-image" :style="{ textAlign: child.settings?.align || 'center' }">
                                    <img v-if="child.image_url" :src="child.image_url" :alt="child.caption || ''" :style="{ maxWidth: child.settings?.width || '100%' }">
                                    <div v-else class="blog-preview-image-placeholder">
                                        <i class="fa fa-image fa-3x"></i>
                                        <p>{{ __('Select an image') }}</p>
                                    </div>
                                    <p v-if="child.caption" class="blog-image-caption">@{{ child.caption }}</p>
                                </div>
                                <div v-else-if="child.type === 'table'" class="blog-preview-table">
                                    <table class="table table-bordered">
                                        <thead v-if="child.settings?.headerRow && child.rows.length">
                                            <tr>
                                                <th v-for="(cell, cci) in child.rows[0]" :key="cci" v-html="cell || '&nbsp;'"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(row, ri) in (child.settings?.headerRow ? child.rows.slice(1) : child.rows)" :key="ri">
                                                <td v-for="(cell, cci) in row" :key="cci" v-html="cell || '&nbsp;'"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="blog-columns-add">
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click.stop="addBlockToColumn(block, ci, 'text')">
                                    <i class="fa fa-font"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click.stop="addBlockToColumn(block, ci, 'image')">
                                    <i class="fa fa-image"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click.stop="addBlockToColumn(block, ci, 'table')">
                                    <i class="fa fa-table"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!blocks.length" class="blog-preview-empty">
                    <i class="fa fa-plus-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">{{ __('Add blocks from the left panel') }}</p>
                </div>
            </div>
        </div>

        <div class="blog-block-settings-zone overflow-auto" v-if="selectedBlock">
                <div class="blog-settings-body">
                    <template v-if="selectedBlock.type === 'text'">
                        <div class="blog-form-field blog-text-editor-wrap">
                            <label>{{ __('Content') }}</label>
                        <text-block-editor :key="selectedBlock.id" v-model="selectedBlock.content"></text-block-editor>
                    </div>
                </template>
                    <template v-if="selectedBlock.type === 'image'">
                        <div class="blog-form-field">
                            <label>{{ __('Image') }}</label>
                            <div class="blog-image-picker">
                            <div v-if="selectedBlock.image_url" class="mb-2">
                                <img :src="selectedBlock.image_url" class="img-fluid rounded">
                            </div>
                            <button class="btn btn-sm btn-secondary" @click="pickImage">
                                <i class="fa fa-folder-open"></i> {{ __('Choose file') }}
                            </button>
                            <button v-if="selectedBlock.image_id" class="btn btn-sm btn-outline-danger ml-1" @click="clearImage">{{ __('Clear') }}</button>
                        </div>
                    </div>
                        <div class="blog-form-field">
                            <label>{{ __('Width') }}</label>
                            <select class="form-control" v-model="selectedBlock.settings.width">
                            <option value="100%">100%</option>
                            <option value="75%">75%</option>
                            <option value="50%">50%</option>
                            <option value="auto">{{ __('Auto') }}</option>
                        </select>
                    </div>
                        <div class="blog-form-field">
                            <label>{{ __('Alignment') }}</label>
                            <select class="form-control" v-model="selectedBlock.settings.align">
                            <option value="left">{{ __('Left') }}</option>
                            <option value="center">{{ __('Center') }}</option>
                            <option value="right">{{ __('Right') }}</option>
                        </select>
                    </div>
                </template>
                    <template v-if="selectedBlock.type === 'table'">
                        <div class="blog-form-field">
                            <label>{{ __('Rows') }}</label>
                        <input type="number" class="form-control" min="1" max="50" :value="selectedBlock.rows.length" @change="setTableRows($event.target.value)">
                    </div>
                        <div class="blog-form-field">
                            <label>{{ __('Columns') }}</label>
                        <input type="number" class="form-control" min="1" max="20" :value="selectedBlock.rows[0]?.length || 2" @change="setTableCols($event.target.value)">
                    </div>
                        <div class="blog-form-field">
                            <label>{{ __('Table data') }}</label>
                        <div class="blog-table-editor">
                            <div v-for="(row, ri) in selectedBlock.rows" :key="ri" class="d-flex mb-1">
                                <input
                                    v-for="(cell, ci) in row"
                                    :key="ci"
                                    type="text"
                                    class="form-control form-control-sm mr-1"
                                    v-model="selectedBlock.rows[ri][ci]"
                                    :placeholder="'R' + (ri+1) + 'C' + (ci+1)"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" :id="'header-' + selectedBlock.id" v-model="selectedBlock.settings.headerRow">
                        <label class="form-check-label" :for="'header-' + selectedBlock.id">{{ __('First row is header') }}</label>
                    </div>
                </template>
                    <template v-if="selectedBlock.type === 'columns'">
                        <div class="blog-form-field">
                            <label>{{ __('Columns') }}</label>
                            <select class="form-control" v-model.number="selectedBlock.settings.count" @change="setColumnCount(selectedBlock.settings.count)">
                                <option :value="1">1</option>
                                <option :value="2">2</option>
                                <option :value="3">3</option>
                                <option :value="4">4</option>
                                <option :value="5">5</option>
                            </select>
                        </div>
                        <div class="blog-form-field" v-if="selectedBlock.settings.count === 2">
                            <label>{{ __('Column ratio') }}</label>
                            <select class="form-control" v-model="selectedBlock.settings.ratio">
                                <option value="50-50">50 / 50</option>
                                <option value="40-60">40 / 60</option>
                                <option value="60-40">60 / 40</option>
                                <option value="33-67">33 / 67</option>
                                <option value="67-33">67 / 33</option>
                            </select>
                        </div>
                    </template>
            </div>
        </div>
        </div>
    </div>
</div>
</div>

<script>
    var blogEditorData = {
        id: {{ $row->id ?? 0 }},
        title: @json($row->title ?? ''),
        slug: @json($row->slug ?? ''),
        status: @json($row->status ?? 'draft'),
        image_id: @json($row->image_id ?? null),
        cover_url: @json($row->getCoverUrl()),
        excerpt: @json($row->excerpt ?? ''),
        content_json: @json($row->content_json ?? ['blocks' => []]),
        last_saved: @json($row->updated_at ? display_datetime($row->updated_at) : ''),
        save_url: @json($save_url),
        csrf_token: @json(csrf_token()),
    };
    var blogEditorI18n = {
        text: @json(__('Text')),
        image: @json(__('Image')),
        table: @json(__('Table')),
        columns_layout: @json(__('Columns layout')),
        untitled: @json(__('Untitled')),
        saved: @json(__('Saved')),
        error: @json(__('Error saving')),
    };
</script>
