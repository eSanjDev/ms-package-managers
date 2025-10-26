class BaseTable {
    constructor(resourceName, entityName, params = {}) {
        this.resourceName = resourceName;
        this.entityName = entityName;
        this.params = params;
        this.dtEntity = null;

        this.params.flagOnlyTrashed = parseInt(window.only_trash || 0);
        this.params.includesRelations = this.params.includesRelations || '';
    }

    init() {
        this._applyTheme();
        this._initializeDataTable();
    }

    _applyTheme() {
        let borderColor, bodyBg, headingColor;

        if (isDarkStyle) {
            ({borderColor, bodyBg, headingColor} = config.colors_dark);
        } else {
            ({borderColor, bodyBg, headingColor} = config.colors);
        }
    }

    _initializeDataTable() {
        const dtEntities = $('.datatables-ajax');
        if (dtEntities.length) {
            this.dtEntity = dtEntities.DataTable({
                processing: true,
                serverSide: true,
                pageLength: 15,
                ajax: this.fetchData.bind(this),
                columns: this.getColumns(),
                columnDefs: this.getColumnDefinitions(),
                order: [['id', 'desc']],
                dom: this.getDomLayout(),
                language: this.getLanguageSettings(),
                buttons: this.getExportButtons(),
                responsive: this.getResponsiveSettings(),
                initComplete: this._initComplete.bind(this),
            });

            this._attachDeleteRecordEvent();
            this._attachRecoveryRecordEvent();

            setTimeout(() => {
                $('.dt-search .form-control').removeClass('form-control-sm');
                $('.dt-length .form-select').removeClass('form-select-sm');
            }, 100);

            this.addCustomPageLengthInput(this.dtEntity);
        }
    }

    _initComplete() {
        this.addCustomFilter(this.dtEntity)
        this._addTrashButtons();

    }

    getDataTableInstance() {
        return this.dtEntity;
    }

    _addTrashButtons() {
        const pageLengthContainer = $('.dt-length');
        const flagOnlyTrashed = this.params.flagOnlyTrashed || 0;

        if (!flagOnlyTrashed) {
            const customBtn = $(`
                <button type="button" id="trash-btn" class="btn btn-label-secondary ms-3 waves-effect waves-light">
                    <i class="ti ti-trash me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">Trash</span>
                </button>
            `);
            pageLengthContainer.parent().append(customBtn);
            $('#trash-btn').on('click', () => {
                window.location = `${baseUrlAdmin}/${this.resourceName}?only_trash=1`;
            });
        } else {
            const customBtn = $(`
                <button type="button" id="trash-back-btn" class="btn btn-label-secondary ms-3 waves-effect waves-light">
                    <i class="ti ti-arrow-back-up ti-sm me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">Back</span>
                </button>
            `);
            pageLengthContainer.parent().append(customBtn);
            $('#trash-back-btn').on('click', () => {
                window.location = `${baseUrlAdmin}/${this.resourceName}`;
            });
        }
    }

    // Attach event for delete record
    _attachDeleteRecordEvent() {
        let $datatableTbody = $('.datatables-ajax tbody');
        $datatableTbody.on('click', '.delete-record', (e) => {
            const row = $(e.target).parents('tr');
            const rowData = this.dtEntity.row(row).data();
            const entityId = rowData.id;

            Swal.fire({
                title: 'Are you sure Delete?',
                text: `Deleting ${this.entityName} (ID: ${entityId}) will remove all data!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
                    cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="csrf"]').val()
                        }
                    });
                    $.ajax({
                        url: `${baseUrlApi}/${this.resourceName}/${entityId}`,
                        method: 'DELETE',
                        success: () => {
                            Swal.fire({
                                icon: 'success',
                                title: `${this.entityName} Deleted!`,
                                text: `${this.entityName} (ID: ${entityId}) deleted successfully.`,
                                customClass: {
                                    confirmButton: 'btn btn-success waves-effect waves-light'
                                }
                            });
                            this.dtEntity.row(row).remove().draw();
                        },
                        error: () => {
                            Swal.fire({
                                title: 'Error!',
                                text: `Error deleting ${this.entityName}.`,
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-success waves-effect waves-light'
                                }
                            });
                        }
                    });
                }
            });
        });
    }

    // Attach event for recovery record
    _attachRecoveryRecordEvent() {
        let $datatableTbody = $('.datatables-ajax tbody');
        $datatableTbody.on('click', '.recovery-record', (e) => {
            const row = $(e.target).parents('tr');
            const rowData = this.dtEntity.row(row).data();
            const entityId = rowData.id;

            Swal.fire({
                title: 'Are you sure Restore?',
                text: `Do you want to restore ${this.entityName} (ID: ${entityId})?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Restore!',
                customClass: {
                    confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
                    cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="csrf"]').val()
                        }
                    });
                    $.ajax({
                        url: `${baseUrlApi}/${this.resourceName}/${entityId}/restore`,
                        method: 'POST',
                        success: (response) => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Restored!',
                                text: response.message,
                                customClass: {
                                    confirmButton: 'btn btn-success waves-effect waves-light'
                                }
                            });
                            this.dtEntity.ajax.reload();
                        },
                        error: (error) => {
                            Swal.fire({
                                title: 'Error!',
                                text: error.responseJSON.message,
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-success waves-effect waves-light'
                                }
                            });
                        }
                    });
                }
            });
        });
    }

    // Method to add custom page length input
    addCustomPageLengthInput(api) {
        const customInput = $('<input type="number" min="1" class="form-control custom-page-length" placeholder="Rows per page">');
        customInput.on('change', function () {
            const val = parseInt($(this).val(), 10);
            if (!isNaN(val) && val > 0) {
                api.page.len(val).draw();
            }
        });
        // Add custom input to dt-length, which is now in the footer
        $('.dt-length').html(customInput);
    }

    addCustomFilter(api) {
        return {}
    }

    // Fetch data for DataTable
    fetchData(data, callback) {
        const page = Math.ceil(data.start / data.length) + 1;
        let searchValue = data.search.value;
        const orderElement = data.order[0];
        const orderColumn = orderElement ? data.columns[orderElement.column].data : 'id';
        const orderDir = orderElement ? orderElement.dir : 'desc';

        let customFilters = this.getCustomFilters(data);

        const flagOnlyTrashed = this.params.flagOnlyTrashed || 0;
        const includesRelations = this.params.includesRelations;

        $.getJSON(`${baseUrlApi}/${this.resourceName}`, {
            page: page,
            per_page: data.length,
            search: searchValue,
            order: orderColumn,
            dir: orderDir,
            includes: includesRelations,
            only_trash: flagOnlyTrashed,
            ...customFilters
        }, (response) => {
            const out = this.transformResponse(response);
            callback({
                draw: data.draw,
                recordsTotal: response.totalRecords,
                recordsFiltered: response.meta.total,
                data: out
            });
        });
    }

    // This method can be customized by each specific table
    getCustomFilters(data) {
        return {};
    }

    transformResponse(response) {
        return response.data.map(entityItem => ({
            id: entityItem.id,
            actions: ''
        }));
    }

    getColumns() {
        return [
            {data: ''},
            {data: 'id'},
            {data: 'actions'}
        ];
    }

    getColumnDefinitions() {
        return [
            {
                className: 'control',
                searchable: false,
                orderable: false,
                responsivePriority: 0,
                targets: 0,
                render() {
                    return '';
                }
            }
        ];
    }

    getDomLayout() {
        return '<"row me-2 my-3"<"col-md-6 mb-2 mb-sm-0 text-start d-flex align-items-center justify-content-center justify-content-md-start"<"dt-search"f><"dt-length ms-3">><"col-md-6 dt-action-buttons text-end d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"B>>' +
            't' +
            '<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>';
    }

    getLanguageSettings() {
        return {
            sLengthMenu: '_MENU_',
            search: '',
            searchPlaceholder: 'Search'
        };
    }

    getExportButtons() {
        const entityName = this.entityName;
        const resourceName = this.resourceName;

        let exportButtons = {
            extend: 'collection',
            className: 'btn btn-label-secondary dropdown-toggle waves-effect waves-light',
            text: '<i class="ti ti-screen-share me-1 ti-xs"></i>Export',
            buttons: ['print', 'csv', 'excel', 'pdf', 'copy'].map(format => ({
                extend: format,
                text: `<i class="ti ti-file-${format === 'copy' ? 'text' : format} me-2"></i>${format.charAt(0).toUpperCase() + format.slice(1)}`,
                className: 'dropdown-item',
                exportOptions: {
                    columns: this.getExportColumns(),
                    format: {
                        body(inner) {
                            const el = $.parseHTML(inner);
                            return el.map(item => item.textContent || item.innerText).join('');
                        }
                    }
                }
            }))
        };

        const hasCreateBtn = this.params.hasCreateBtn !== false;

        return hasCreateBtn ? [
            exportButtons,
            {
                text: `<i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">Add New ${entityName}</span>`,
                className: 'add-new btn btn-primary waves-effect waves-light',
                action() {
                    window.location = `${baseUrlAdmin}/${resourceName}/create`;
                }
            }
        ] : [exportButtons];
    }

    getExportColumns() {
        return [1, 2, 3, 5, 6];
    }

    getResponsiveSettings() {
        return {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({
                    header(row) {
                        const data = row.data();
                        return `Details of ${data.title || data.name || data.code || data.id}`;
                    }
                }),
                type: 'column',
                renderer(api, rowIdx, columns) {
                    const data = columns.map(col => col.title ? `<tr><td>${col.title}:</td><td>${col.data}</td></tr>` : '').join('');
                    return data ? $('<table class="table"/><tbody />').append(data) : false;
                }
            }
        };
    }

    getUrlParam(paramName) {
        const url = new URL(window.location.href);

        const params = new URLSearchParams(url.search);

        return params.get(paramName);
    }
}

export default BaseTable;
