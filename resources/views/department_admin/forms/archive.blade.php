@extends('layouts.admin.master')
@section('title')
    Biễu Mẫu Đã Bị Xóa
@endsection
@section('content')
    <div class="container-fluid">
        @include('common.errors')
        <br />
        <br />
        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Danh Sách Biễu Mẫu Đã Bị Xóa</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead class="frm-align">
                        <tr>
                            <th>ID</th>
                            <th>Tên biễu mẫu</th>
                            <th>Tải về</th>
                            <th>Mô tả</th>
                            <th>Hành động</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($forms as $form)
                            <tr>
                                <th scope="row">{{ $form->id }}</th>
                                <td>{{ $form->name }}</td>
                                <td><a href="{{ route('forms.download',$form->id) }}">{{ $form->link }}</a> </td>
                                <td>{{ $form->description }}</td>
                                <td>
                                    <ul class="d-flex" style="list-style-type: none;margin-left: -16px;">
                                        {!!Form::open(["method" => "PUT", "route" => ["forms.archive.restore", $form->id ], "id" => "restoreArchive".$form->id])!!}
                                        <a href="javascript:void(0)" class="text-success data-delete frm-margin-left-8" onclick="restoreArchivedData('restoreArchive'+{{$form->id}})" title="Khôi phục">
                                            <i class="fa fa-trash-restore"></i>
                                        </a>
                                        {!!Form::close()!!}
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
