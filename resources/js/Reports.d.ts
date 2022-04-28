interface ReportConnection {
    default: boolean
    name: string
    value: string
}

interface ReportOptions {
    name: String
    value: string
}

interface IReport {
    id?: number
    name: string | null
    description: string | null
    connection?: string | null
    display_limit: number
    table?: string | null
    where?: string | null
    groupby?: string | null
    having?: string |null
    orderby?: string | null
    show_data: boolean
    show_totals: boolean
    admin_only?: boolean
    active: boolean
    created_at?: Date
    updated_at?: Date
    deleted_at?: Date | null
    middleware: Array<ReportMiddleware>
    fields: Array<ReportField>
    selects: Array<ReportSelect>
    joins: Array<ReportJoin>
}

interface RenderRender {
    active: boolean
    connection: string
    deleted_at: Date | string | null
    description: string
    display_limit: number
    export_drivers: Array<ReportOptions>
    groupby: string | null
    having: string | null
    id: number
    name: string
    orderby: string | null
    queued_export_drivers: Array<ReportOptions>
    show_data: boolean
    show_totals: boolean
    table: string
    where?: string
    middleware: Array<ReportMiddleware>
    fields: Array<ReportField>
    selects: Array<ReportSelect>
    joins: Array<ReportJoin>
}

interface ReportResults {
    data: []
    drivers: Array<ReportOptions>
    headings: Array<string>
    parameters: []
    raw?: string
    result_limit: number
    totals: boolean
}

interface ReportMiddleware {
    id: number | null
    middleware: string | null
    deleted_at: Date | string | null
}

interface ReportField {
    id: number
    label: string
    type: 'text' | 'number' | 'date' | 'datetime' | 'select'
    model: string | null
    alias: string
    options?: [],
    model_select_value: string | null
    model_select_name: string | null
    deleted_at: Date | string | null
}

interface ReportSelect {
    id: number
    column: string
    alias: string
    type: string | null
    column_order: number
    deleted_at: Date | string | null
}

interface ReportJoin {
    id: number
    type: 'inner_join' | 'left_join' | 'right_join'
    table: string
    first: string
    operator: string
    second: string
    deleted_at: Date | string | null
}

interface QueuedReportJob {
    authenticatable_id: string
    created_at: Date | string
    deleted_at: Date | string | null
    exception: string | null
    formatted_parameters: [],
    parameters: [],
    processed: number
    query?: string
    report_id: number | string
    schedule_id: number | string | null
    status: 'scheduled' | 'running' | 'complete' | 'failed'
    total: number
    updated_at: Date | string
    uuid: string
    report_name: string
}

interface ReportExport {
    chunk_limit: number,
    date: Date,
    name: string,
    total: number
    urls: Array<ReportExportUrls>
    uuid: string
}

interface ReportExportUrls {
    name: string
    url: string
}

export {ReportConnection, ReportOptions, IReport, RenderRender, ReportResults, ReportMiddleware, ReportField, ReportSelect, ReportJoin, QueuedReportJob, ReportExport}
