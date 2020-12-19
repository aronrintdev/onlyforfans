@extends('layouts.app2')

@section('content')
  <div class="row mt-5">
    <div class="col-sm-12">
      <my-saved 
        :vault_pkid="{{ $myVault->id }}"
        :vaultfolder_pkid="{{ $vaultRootFolder->id }}"
        ></my-saved>
    </div>
  </div>
@endsection
