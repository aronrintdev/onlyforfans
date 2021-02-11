@extends('layouts.app2')

@section('content')
  <div id="view-saved_dashboard" class="row">
    <div class="col-sm-12">
      <my-saved 
        :vault_pkid="{{ $myVault->id }}"
        :vaultFolder_pkid="{{ $vaultRootFolder->id }}"
        ></my-saved>
    </div>
  </div>
@endsection
