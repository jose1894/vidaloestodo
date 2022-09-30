@extends('admin.layouts.app')

@section('panel')

    <div class="row">

        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive table-responsive-xl">
                        <table class=" table align-items-center table--light">
                            <thead>
                            <tr>
                                <th>Código cCrto</th>
                                <th>Descripción</th>
                            </tr>
                            </thead>
                            <tbody class="list">
                            <tr>
                                <td>@{{name}}</td>
                                <td>@lang('User Name')</td>
                            </tr>
                            <tr>
                                <td>@{{message}}</td>
                                <td>@lang('Message')</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        <div class="col-lg-12">
            <div class="card mt-5">
                <form action="{{ route('admin.email_template.global') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">De</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control form-control-lg" placeholder="Dirección de Correo" name="email_from" value="{{ $general_setting->email_from }}"  required/>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="font-weight-bold">Cuerpo del Correp</label>
                            </div>
                            <div class="col-md-10">
                                <textarea name="email_template" rows="10" class="form-control form-control-lg nicEdit" placeholder="Tu Plantilla de Correo">{{ $general_setting->email_template }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-block btn--primary mr-2">Modificar</button>
                    </div>
                </form>
            </div><!-- card end -->
        </div>


    </div>

@endsection


