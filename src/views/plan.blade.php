@extends('msdev2::layout.master')
@section('content')
<header>
    <h1>Pricing Plan</h1>
    <h2>Select package which you want to select</h2>
</header>
<section>
    <article class="plist">

    </article>
</section>
@endsection
@section('style')
<style>
.card.active,.card:hover {
    transform: scale(1.05);
}
</style>
@endsection
@section('scripts')
    @parent
    <script type="text/javascript">
        var currentPlan = '{{$activePlanName}}';
        var appUsed = parseInt('{{$appUsed}}');
        var pricingPlan = {!! json_encode(config('msdev2.plan')) !!};
        var classList = ['twelve','six','four','three']
        var el = [];
        var column = classList[pricingPlan.length-1];
        pricingPlan.forEach((element,key) => {
            let iType = ''
            if(element.interval == "EVERY_30_DAYS"){
                iType = '/month'
            }
            else if(element.interval == "ANNUAL"){
                iType = '/year'
            }
            else if(element.interval == "ONE_TIME"){
                iType = ' One Time'
            }
            let feature = [];
            (element.feature).forEach(element => {
                let cls = ''
                if(element.value=='true'){
                    cls = 'success'
                }else if(element.value=='false'){
                    cls = 'error'
                }
                feature.push(`<tr class="${cls}"><td>
                    <div>${element.value=='true' ? '✅' : '❌ '}
                        ${element.name}
                        ${element.help_text ? ' &nbsp; <span class="tip" data-hover="'+element.help_text+'" style="position: relative;top: -5px;"><i class="icon-question"></i></span>' : ''}
                    </div>
                </td></tr>`)
            });
            let buttonForm = `<button type="button" disabled="disabled">Current Plan</button>`;
            let isActive = 'active'
            if(currentPlan != element.chargeName){
                isActive = ''
                buttonForm = `<form target="_parent" method="post" action="{{Msdev2\Shopify\Utils::getUrl(route('msdev2.shopify.plan.subscribe'))}}">
                    <input type="hidden" name="plan" value="${element.chargeName}">`;
                if(appUsed < element.trialDays && element.trialDays > 0){
                    buttonForm += `<button>${'Start '+ (element.trialDays-appUsed) +' Day Trial'}</button>`
                }else{
                    buttonForm += `<button>Purchase</button>`
                }
                buttonForm += `</form>`;
            }
            el.push(`<div class="columns ${column}"><div class="card ${isActive}">
                <table><thead><tr><th>
                    <div>${element.chargeName}</div>
                    <h2>${element.amount == 0 ? '' : element.currencyCode}${element.amount == 0 ? 'Free' : element.amount }${iType}</h2>
                </th></tr></thead>
                <tbody>
                    ${feature.join('')}
                </tbody>
                <tfoot><tr><td>
                    ${buttonForm}
                </td></tr></tfoot>
                </table>
            </div></div>`)
        });
        document.querySelector('.plist').innerHTML = el.join('')
        actions.TitleBar.create(app, { title: 'Plan List' });
    </script>
@endsection