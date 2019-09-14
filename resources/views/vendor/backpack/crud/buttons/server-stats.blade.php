@if ($entry->status == \App\Models\VPS\Server::STATUS_ENABLE)
    <button class="btn btn-xs btn-default stats-btn" data-server-id="{{ $entry->id }}">
        <i class="fa fa-safari"></i> Stats
    </button>
@else
    <a class="btn btn-xs btn-default" href="{{ backpack_url('order/order?server_id='.$entry->ip) }}">
        <i class="fa fa-files-o"></i> Orders
    </a>
@endif

<script>
    $(document).ready(function() {
        $('.stats-btn').click(function (e) {
            e.preventDefault();
            var server_id = $(this).data('server-id')
            $('.stats-btn').attr('disabled', true);
            window.location.href = "{{ backpack_url('vps/server/stats/') }}"+'/'+server_id;
        })
    });
</script>