header("content-type:text/html;charset=utf-8");
$total = 10;
$num = 8;
$min = 0.01;
for ($i=1; $i< $num ; $i++) {
    $safe_total = ($total-($num-$i)*$min)/($num-$i);//随机安全上限
    $money = mt_rand($min*100,$safe_total*100)/100;
    $total = $total-$money;
    echo '第'.$i.'个红包：'.$money.'元，余额：'.$total.'元 <br />';
}
echo '第'.$num.'个红包：'.$total.'元，余额：0元 ';
?>
