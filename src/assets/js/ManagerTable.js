import BaseTable from './BaseTable.js';

class ManagerTable extends BaseTable {
    constructor() {
        super('managers', 'manager');
    }

    getColumns() {
        let columns = [
            {data: ''},
            {data: 'id'},
            {data: 'role'},
            {data: 'status'},
            {data: 'created_at'}
        ];

        if (this.params.flagOnlyTrashed) {
            columns.push({data: 'deleted_at', title: 'Deleted At'});
        }

        columns.push({data: 'actions'});

        return columns;
    }

    getCustomFilters() {
        return {}
    }

    getColumnDefinitions() {
        const flagOnlyTrashed = this.params.flagOnlyTrashed;
        const resourceName = this.resourceName;

        return [
            ...super.getColumnDefinitions(),
            {
                targets: 1,
                data: 'name',
                title: 'Name',
                render(data, type, full) {
                    return `<strong>${full.name}</strong>`;
                }
            },
            {
                targets: 2,
                data: 'role',
                title: 'Role',
                render(data, type, full) {
                    return `<span>${full.role}</span>`;
                }
            },
            {
                targets: 3,
                data: 'last_login',
                title: 'Last Login',
                render(data, type, full) {
                    return `<span>${full.last_login}</span>`;
                }
            },
            {
                targets: 4,
                data: 'status',
                title: 'Status',
                render(data, type, full) {
                    const statusClass = full.status ? 'bg-label-success' : 'bg-label-secondary';
                    const statusText = full.status ? 'Active' : 'Inactive';
                    return `<span class="badge ${statusClass}">${statusText}</span>`;
                }
            },
            {
                targets: -1,
                responsivePriority: 6,
                title: 'Actions',
                searchable: false,
                orderable: false,
                render(data, type, full) {
                    return flagOnlyTrashed ? `
                        <div class="d-flex align-items-center">
                            <a href="javascript:;" class="text-body recovery-record"><i class="ti ti-reload ti-sm mx-2"></i></a>
                        </div>` : `
                        <div class="d-flex align-items-center">
                            <a href="javascript:;" class="text-body delete-record"><i class="ti ti-trash ti-sm mx-2"></i></a>
                            <a href="${window.baseUrlWeb}/${resourceName}/${full['id']}/edit" class="text-body"><i class="ti ti-edit ti-sm me-2"></i></a>
                        </div>
                    `;
                }
            }
        ];
    }

    transformResponse(response) {
        return response.data.map(entityItem => ({
            id: entityItem.id,
            esanj_id: entityItem.esanj_id,
            name: entityItem.name,
            role: entityItem.role,
            status: entityItem.is_active,
            last_login: entityItem.last_login,
            deleted_at: entityItem.deleted_at,
            actions: ''
        }))
    }
}

const managerTable = new ManagerTable();

managerTable.init();
