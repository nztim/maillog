@extends('emails.template')

@section('main')
    @include('emails._image-left', ['src' => asset('images/logo-220x24.png')])
    <br>
    <h1>System Email Failure</h1>
    <table>
        <tr>
            <th style="width: 100px;text-align:left;">Sender:</th>
            <td>{{ $sender }}</td>
        </tr>
        <tr>
            <th style="width: 100px;text-align:left;">Recipient:</th>
            <td>{{ $recipient ?? 'Unknown' }}</td>
        </tr>
        <tr>
            <th style="width: 100px;text-align:left;">Subject:</th>
            <td>{{ $subject ?? 'Unknown' }}</td>
        </tr>
        <tr>
            <th style="width: 100px;text-align:left;">Problem:</th>
            <td>{{ $type ?? 'Unknown' }}</td>
        </tr>
        <tr>
            <th style="width: 100px;text-align:left;">Description:</th>
            <td>{{ $description ?? 'Unknown' }}</td>
        </tr>
    </table>
    <br>
    <p>
        <a href="{{ route('admin.maillog.show', $id) }}">
            <strong>View failed message</strong>
        </a>
    </p>
@stop
