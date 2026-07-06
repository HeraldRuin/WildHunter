export const mediaMoveMixin = {
    methods: {
        parseDragPayload(event) {
            const raw = event.dataTransfer.getData('application/json');
            if (!raw) {
                return null;
            }

            try {
                return JSON.parse(raw);
            } catch (e) {
                return null;
            }
        },
        allowMediaDrop(event) {
            event.preventDefault();
            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'move';
            }
        },
        handleMediaDrop(targetFolderId, event) {
            event.preventDefault();
            event.stopPropagation();

            const payload = this.parseDragPayload(event);
            if (!payload) {
                return;
            }

            if (payload.type === 'folder') {
                this.moveFolder(payload.id, targetFolderId);
                return;
            }

            if (payload.type === 'files' && payload.ids && payload.ids.length) {
                this.moveFiles(payload.ids, targetFolderId);
            }
        },
        moveFolder(folderId, parentId) {
            if (!folderId || folderId === parentId) {
                return;
            }

            const me = this;
            me.isLoading = true;

            $.ajax({
                url: bookingCore.url + '/media/folder/move',
                type: 'post',
                data: {
                    id: folderId,
                    parent_id: parentId
                },
                dataType: 'json',
                success: function (json) {
                    me.isLoading = false;
                    if (json.status) {
                        me.reloadAll();
                    }
                },
                error: function (e) {
                    me.isLoading = false;
                    bookingCoreApp.showAjaxError(e);
                }
            });
        },
        moveFiles(fileIds, folderId) {
            if (!fileIds || !fileIds.length) {
                return;
            }

            const me = this;
            me.isLoading = true;

            $.ajax({
                url: bookingCore.admin_url + '/module/media/moveFiles',
                type: 'post',
                data: {
                    file_ids: fileIds,
                    folder_id: folderId
                },
                dataType: 'json',
                success: function (json) {
                    me.isLoading = false;
                    if (json.status) {
                        me.resetSelected();
                        me.reloadAll();
                    }
                },
                error: function (e) {
                    me.isLoading = false;
                    bookingCoreApp.showAjaxError(e);
                }
            });
        }
    }
};
