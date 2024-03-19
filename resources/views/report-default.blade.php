@extends('layouts.app')

@section('title',__('Inventory Report'))

@section('content')
<div class="container dashboard-widgets">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body px-2 py-2">
                    <report
                        report-name="{{ __($report_name) }}"
                        fields-string="{{ json_encode($field_links) }}"
                        record-string="{{ json_encode($data) }}"
                        download-url="{{ request()->fullUrlWithQuery(['filename' =>  __($report_name).'.csv']) }}"
                        download-button-text="{{ __('Download All') }}"
                    ></report>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
