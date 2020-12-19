@extends('layouts.app2')

@section('content')
  <div class="row mt-5">
    <div class="col-sm-12">
      <my-vault 
        :vault_pkid="{{ $myVault->id }}"
        :vaultfolder_pkid="{{ $vaultRootFolder->id }}"
        ></my-vault>
    </div>
  </div>
{{--
         :children="{{ json_encode($children) }}"
         :mediafiles="{{ json_encode($mediafiles) }}" 
         :cwf="{{ $cwf }}" 
         :parent="{{ json_encode($parent) }}"
         username="{{ $sessionUser->username }}" 
--}}
@endsection
