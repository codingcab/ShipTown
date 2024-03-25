@extends('layouts.app')

@section('title',__('Inventory Report'))

@section('content')
    <report
        report-name="{{ __($report_name) }}"
        fields-string="{{ json_encode($field_links) }}"
        record-string="{{ json_encode($data) }}"
        download-url="{{ request()->fullUrlWithQuery(['filename' =>  __($report_name).'.csv']) }}"
        download-button-text="{{ __('Download All') }}"
        pagination-string="{{ json_encode($pagination) }}"
    ></report>
@endsection
