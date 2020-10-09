# parspal-api-helper
<p dir='rtl' align='right'>کلاس کمکی اتصال به API درگاه پرداخت پارس پال</p>

<h3 dir='rtl' align='right'>راهنمای استفاده</h3>

<p dir='rtl' align='right'>به منظور دسترسی به کلاس می بایست فایل را در مسیری از پروژه یا هاست درج و آن را به پروژه include نمایید</p>

```php
require_once(dirname(__FILE__) . '/parspal-api-helper.class.php');
```
<h4 dir='rtl' align='right'>نمونه کد ارسال درخواست پرداخت</h4>

<p dir='rtl' align='right'>پیش از ارسال درخواست می بایست مقادیر مورد نیاز را مقدار دهی نمایید
<br> 
کلید API درگاه پرداخت پارس پال :</p>

```php
$apikey = '00000000-0000-0000-0000-000000000000';
```
<p dir='rtl' align='right'>سایر اطلاعات مورد نیاز :</p>

```php
$amount = 1000; // مبلغ
$order_id = '1'; // شماره سفارش - این مقدار در بازگشت به مسیر بازگشت ارسال می گردد
$reserve_id = '10001';// شماره رزرو - این مقدار در بازگشت به مسیر بازگشت ارسال می گردد
$description = 'توضیحات پرداخت';

$callbackUrl = 'مسیر برگشت';

// ارسال این اطلاعات به اختیاری و در صورت تمایل می توانید به صورت کامل از آرایه حذف نمایید
$name = 'نام پرداخت کننده';
$email = 'ایمیل پرداخت کننده';
$mobile = 'موبایل پرداخت کننده'
```
<p dir='rtl' align='right'>ارسال درخواست و دریافت نتیجه :</p>

```php
$papi = new ParsPal_API_Helper($apikey);

$rapi = $papi->paymentRequest(array(
        'amount'      => $amount,
        'order_id'    => $order_id,
        'reserve_id'  => $reserve_id,
        'description' => $description,
        'return_url'  => $callbackUrl,
        'currency'    => 'IRR',
        'payer'       => array(
            'name'        => $name,
            'email'       => $email,
            'mobile'      => $mobile,
)));
```
<p dir='rtl' align='right'>هدایت کاربر به درگاه پرداخت و یا هندل خطا های احتمالی :</p>

```php
if ($rapi)
{
    // مسیر پرداخت - کاربر می بایست به این مسیر منتقل شود
    $paymentUrl = $papi->paymentUrl;
}
else
{
    // خطای ثبت درخواست
    $error_message = $papi->error;
}
```
<p dir='rtl' align='right'>در حالتی که نتیجه درخواست موفق دریافت شود می بایست کاربر به مسیر دریافتی $paymentUrl هدایت گردد .</p>

<h4 dir='rtl' align='right'>نمونه کد تایید پرداخت</h4>
<p dir='rtl' align='right'>ابتدا می بایست مقادیر ارسالی را دریافت نمایید :</p>

```php
$order_id = $_POST['order_id']; // مقدار ارسال شده در هنگام درخواست پرداخت
$reserve_id = $_POST['reserve_id']; // مقدار ارسال شده در هنگام درخواست پرداخت
```

<p dir='rtl' align='right'>پس از می بایست ابتدا وضعیت ارسالی را بررسی و سپس شماره رسید پرداخت را به منظور تایید به API ارسال نمایید :</p>

```php
if($papi->checkStatusCode($_POST['status']))
{
    $receipt_number = $_POST['receipt_number']; // شماره رسید پرداخت پارس پال
    
    $rapi = $papi->paymentVerify($receipt_number, $amount,'IRR');
    if ($rapi)
    {
        // عملیات تایید پرداخت با موفقیت انجام گردید
        // کد های تحویل محصول یا تکمیل سفارش می بایست در این بخش قرار گیرد
    }
    else
    {
        $error_warning = 'خطا در عملیات پردازش پرداخت :' . '<br>' . 'کد خطا: ' . $papi->error;
    }
}
else
{
    $error_warning = 'اطلاعات مبنی بر پرداخت موفق دریافت نشده است ' . '<br>' . $papi->error;
}
```

<p dir='rtl' align='right'>در صورت دریافت مقدار موفقیت آمیز از تابع paymentVerify شما می بایست فرآیند خرید کاربر را تکمیل و محصول را تحویل و یا سفارش را تکمیل نمایید .</p>

<h4 dir='rtl' align='right'>توضیحات</h4>

<p dir='rtl' align='right'>این کلاس تنها برای استفاده از <a href="https://www.parspal.com/">درگاه پرداخت آنلاین پارس پال</a> پیاده سازی شده است و برای استفاه از آن می بایست در <a href="https://panel.parspal.com/">پنل پرداخت یاری پارس پال</a> عضو و نسبت به ثبت درگاه و دریافت کلید اتصال به API اقدام نمایید .</p>
<ul dir='rtl' align='right'>
  <li><a href="https://developer.parspal.com/">مرکز راهنمایی توسعه دهندگان پارس پال</a></li>
  <li><a href="https://www.parspal.com/plugins/download">پلاگین های آماده اتصال به درگاه پرداخت آنلاین پارس پال</a></li>
</ul>
