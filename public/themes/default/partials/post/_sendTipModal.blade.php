<!-- %VIEW: themes/default/partials/post/_sendTipModal -->
<div id="sendTipModal{{ $post->id }}" class="tip-modal modal fade subscriberFilterModal" role="dialog" tabindex='1'>

  <input type="hidden" value="{{$post->id}}" id="post-id">

  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h3 style="margin: 0;">Send a Tip</h3>
        <button type="button" style="" class="close close-post-modal" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body no-padding">
        <div class="panel panel-default panel-post animated" style="margin-bottom: 0">
          <div class="panel-heading no-bg">
            <div class="post-author">
              <div class="user-avatar">
                <a href="{{ url($post->user->username) }}"><img src="{{ $post->user->avatar->filepath }}" alt="{{ $post->user->name }}" title="{{ $post->user->name }}"></a>
              </div>
              <div class="user-post-details">
                <ul class="list-unstyled no-margin">
                  <li>
                    <a href="{{ url($post->user->username) }}" title="{{ '@'.$post->user->username }}" data-toggle="tooltip" data-placement="top" class="user-name user">
                      {{ $post->user->name }}
                    </a>
                    @if($post->user->verified)
                      <span class="verified-badge bg-success">
                        <i class="fa fa-check"></i>
                      </span>
                    @endif
                  </li>
                  <li>
                    {{ '@'.$post->user->username }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="panel-body">
            <ul>
              <li>
                <p>Tip Amount:</p>
                <div class="total-tipped">
                  <span class="decrement"><i class="fa fa-minus" aria-hidden="true"></i></span>
                  <span class="increment"><i class="fa fa-plus" aria-hidden="true"></i></span>
                  <input min="0" type="number" id="etTipAmount" value="10" data-id="2"  name="total_tipped" class="filter-input total-tipped-input  form-control etTipAmount">
                  <div class="text-wrapper"></div>
                </div>
              </li>
            </ul>
            <textarea name="tip_note" id="tipNote" cols="60" rows="5" style="width: 100%" placeholder="Write a message..."></textarea>
          </div>
          <div class="panel-footer">
            {{-- <a href="#" id="cancelSendTip" class="text-primary btn" data-dismiss="modal">{{ trans('common.cancel') }}</a> --}}
            @if(true || $sessionUser->is_payment_set)
              <button type="button" id="sendTip" class="btn btn-primary sendTip" >{{ trans('common.send_tip') }}</button>
            @else
              <a href="{{url($sessionUser->username).'/settings/addpayment' }}" style="width: auto" id="addPayment" class="btn btn-warning">{{ trans('common.add_payment') }}</a>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
