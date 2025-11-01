import BaseTable from './BaseTable.js';

class ManagerTable extends BaseTable {
    constructor() {
        super('managers', 'managers', {
            includesRelations: ``, hasCreateBtn: false, hasTrashButtons: false,
        });
    }

    getColumns() {
        return [
            {data: ''},
            {data: 'id'},
            {data: 'type'},
            {data: 'created_at'},
            {data: 'actions'}
        ];
    }

    getCustomFilters() {
        return {}
    }

    _addTrashButtons() {
        return [];
    }

    getColumnDefinitions() {
        this.resourceName = `${this.resourceName}/${window.manager_id}/activities`;

        return [
            ...super.getColumnDefinitions(),
            {
                targets: 1,
                data: 'id',
                title: 'Id',
                render(data, type, full) {
                    return `<strong>${full.id}</strong>`;
                }
            },
            {
                targets: 2,
                data: 'type',
                title: 'Type',
                render(data, type, full) {
                    return `<span>${full.type}</span>`;
                }
            },
            {
                targets: 3,
                data: 'created_at',
                title: 'Created AT',
                render(data, type, full) {
                    return `<span>${full.created_at}</span>`;
                }
            },
            {
                targets: -1,
                responsivePriority: 6,
                title: 'Actions',
                searchable: false,
                orderable: false,
                render(data, type, full) {
                    return `
                        <div class="d-flex align-items-center">
                            <a href="javascript:void(0)" class="text-body"><i data-id="${full.id}" class="icon-base ti tabler-eye"></i></a>
                        </div>
                    `;
                }
            }
        ];
    }

    transformResponse(response) {
        return response.data.map(entityItem => ({
            id: entityItem.id,
            type: entityItem.type,
            created_at: entityItem.created_at,
            actions: ''
        }))
    }
}

const managerTable = new ManagerTable();

managerTable.init();

$(document).on('click', '.tabler-eye', function () {
    let id = $(this).data('id')
    let modal = $("#modalActivity")

    Swal.showLoading()

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="csrf"]').val()
        }
    });
    $.ajax({
        url: `/${window.baseUrlApi}/managers/${window.manager_id}/activities/${id}`,
        method: 'GET',
        success: (res) => {
            Swal.close()

            let data = res.data

            modal.find('.modal-body h2').html(`Log ${data.id}`)
            modal.find('.modal-body span').html(data.created_at + " | " + data.type)
            modal.find('.modal-body p').html(JSON.stringify(data.meta))

            modal.modal("show")
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

})

