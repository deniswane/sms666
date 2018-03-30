@extends('layouts.app')
@section('content')
    {{--加这句 靠中间了--}}
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">RECHARGE</div>
            <div class="panel-body">

                <table class="table table-striped">
                    <tr>
                        <td><strong>user</strong></td>
                        <td>{{ Auth::user()->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>amount</strong></td>
                        <td>{{ $m_amount }}</td>
                    </tr>
                    <tr>
                        <td><strong>currency</strong></td>
                        <td>{{ $m_curr }}</td>
                    </tr>
                    <tr>
                        <td><strong>order</strong></td>
                        <td>{{ $m_orderid }}</td>
                    </tr>

                </table>

                <form method="post" action="https://payeer.com/merchant/">
                    {{ csrf_field() }}
                    <input type="hidden" name="m_shop" value="<?=$m_shop?>">
                    <input type="hidden" name="m_orderid" value="<?=$m_orderid?>">
                    <input type="hidden" name="m_amount" value="<?=$m_amount?>">
                    <input type="hidden" name="m_curr" value="<?=$m_curr?>">
                    <input type="hidden" name="m_desc" value="<?=$m_desc?>">
                    <input type="hidden" name="m_sign" value="<?=$sign?>">
                    <?php /*
    <input type="hidden" name="form[ps]" value="2609">
    <input type="hidden" name="form[curr[2609]]" value="USD">
    */ ?>
                    <?php /*
    <input type="hidden" name="m_params" value="<?=$m_params?>">
    */ ?>
                    <input type="submit" class="btn btn-primary" name="m_process" value="send"/>
                </form>
            </div>
        </div>
    </div>



@endsection