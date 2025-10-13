---
title: Code Sample
subtitle: Using Pygments
date: 2016-03-08
---

The following is a code sample using the "highlight" shortcode provided in Hugo.

<!--more-->

{{< highlight javascript >}}
    var num1, num2, sum
    num1 = prompt("Enter first number")
    num2 = prompt("Enter second number")
    sum = parseInt(num1) + parseInt(num2) // "+" means "add"
    alert("Sum = " + sum)  // "+" means combine into a string
{{</ highlight >}}