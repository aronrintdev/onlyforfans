@extends('layouts.app2')

@section('content')
  <div id="view-vault_dashboard" class="row">
    <div class="col-sm-12">
      <my-vault 
        :vault_pkid="{{ $myVault->id }}"
        :vaultfolder_pkid="{{ $vaultRootFolder->id }}"
        ></my-vault>
    </div>
  </div>
@endsection
