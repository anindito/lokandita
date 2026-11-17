"""Teks Inggris dan Arab untuk soal DDP101 pertemuan 2 (lihat buat-ddp101-pertemuan-2.py).

Tiap soal: (judul, pernyataan, format masukan, format keluaran, batasan). Ditulis
oleh agen penyusun; di berkas impor semuanya bertanda `machine: true`.
"""
TERJEMAHAN = {
 "tukar-dua-pecahan": {
  "en": ("Swap Two Real Numbers", "Given two real numbers **x** and **y**, print them in reverse order: **y** first, then **x**.",
         "One line with two real numbers x and y, separated by one space.", "One line: y, one space, then x.", "1 ≤ x, y < 1000, at most two digits after the decimal point."),
  "ar": ("تبديل عددين عشريين", "إذا أُعطيت عددين عشريين **x** و **y** فاطبعهما بترتيب معكوس: **y** أولًا ثم **x**.",
         "سطر واحد فيه عددان عشريان x و y تفصل بينهما مسافة واحدة.", "سطر واحد: y ثم مسافة ثم x.", "1 ≤ x, y < 1000، برقمين على الأكثر بعد الفاصلة العشرية.")},
 "rata-rata-tiga-nilai": {
  "en": ("Average of Three Scores", "Three exam scores are integers. Compute their average as a real number.\n\nRemember: dividing two integers in C++ throws away the fraction.",
         "One line with three integers.", "One number: the average of the three.", "0 ≤ each score ≤ 100"),
  "ar": ("متوسط ثلاث درجات", "ثلاث درجات امتحان أعداد صحيحة. احسب متوسطها كعدد عشري.\n\nتذكّر: قسمة عددين صحيحين في ++C تُسقط الجزء الكسري.",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "عدد واحد: متوسط الثلاثة.", "0 ≤ كل درجة ≤ 100")},
 "luas-keliling-persegi-panjang": {
  "en": ("Area and Perimeter of a Rectangle", "A rectangle has an integer length and width, each at most {{batas}}.\n\nPrint its area, then its perimeter.",
         "One line with the length and the width.", "First line: the area. Second line: the perimeter.", "1 ≤ length, width ≤ {{batas}}"),
  "ar": ("مساحة المستطيل ومحيطه", "مستطيل طوله وعرضه عددان صحيحان، كل منهما لا يزيد على {{batas}}.\n\nاطبع مساحته ثم محيطه.",
         "سطر واحد فيه الطول والعرض.", "السطر الأول: المساحة. السطر الثاني: المحيط.", "1 ≤ الطول، العرض ≤ {{batas}}")},
 "jumlah-dan-selisih": {
  "en": ("Sum and Difference", "Given two integers **a** and **b**, print their sum, then their difference (**a − b**).\n\nThe difference may be negative.",
         "One line with a and b.", "First line: a + b. Second line: a − b.", "1 ≤ a, b ≤ 1000"),
  "ar": ("المجموع والفرق", "إذا أُعطيت عددين صحيحين **a** و **b** فاطبع مجموعهما ثم الفرق بينهما (**a − b**).\n\nقد يكون الفرق سالبًا.",
         "سطر واحد فيه a و b.", "السطر الأول: a + b. السطر الثاني: a − b.", "1 ≤ a, b ≤ 1000")},
 "kuadrat-dan-kubik": {
  "en": ("Square and Cube", "Given an integer **n**, print **n²**, then **n³**.", "One integer n.", "First line: n × n. Second line: n × n × n.", "1 ≤ n ≤ 1000"),
  "ar": ("المربع والمكعب", "إذا أُعطيت عددًا صحيحًا **n** فاطبع **n²** ثم **n³**.", "عدد صحيح واحد n.", "السطر الأول: n × n. السطر الثاني: n × n × n.", "1 ≤ n ≤ 1000")},
 "hasil-bagi-dan-sisa": {
  "en": ("Quotient and Remainder", "**a** sweets are shared equally among **b** children. Every child gets the same number, as many as possible.\n\n"
         "Print how many sweets each child gets, then how many are left over.\n\n"
         "In C++, `/` on two integers gives the quotient rounded down, and `%` gives the remainder.",
         "One line with a and b.", "First line: sweets per child. Second line: sweets left over.", "1 ≤ a ≤ 10000, 1 ≤ b ≤ 100"),
  "ar": ("خارج القسمة والباقي", "تُوزَّع **a** قطعة حلوى بالتساوي على **b** طفلًا. يأخذ كل طفل العدد نفسه وبأكبر قدر ممكن.\n\n"
         "اطبع عدد القطع لكل طفل ثم عدد القطع المتبقية.\n\n"
         "في ++C يعطي `/` بين عددين صحيحين خارج القسمة مقرَّبًا إلى الأسفل، ويعطي `%` الباقي.",
         "سطر واحد فيه a و b.", "السطر الأول: القطع لكل طفل. السطر الثاني: القطع المتبقية.", "1 ≤ a ≤ 10000، 1 ≤ b ≤ 100")},
 "detik-ke-menit": {
  "en": ("Seconds to Minutes", "A video lasts **d** seconds. Write that length in minutes and seconds.\n\nExample: 135 seconds is 2 minutes 15 seconds.",
         "One integer d.", "One line: the number of minutes, one space, then the remaining seconds.", "0 ≤ d ≤ 100000"),
  "ar": ("من الثواني إلى الدقائق", "مدة مقطع فيديو **d** ثانية. اكتب هذه المدة بالدقائق والثواني.\n\nمثال: 135 ثانية هي دقيقتان و 15 ثانية.",
         "عدد صحيح واحد d.", "سطر واحد: عدد الدقائق ثم مسافة ثم الثواني المتبقية.", "0 ≤ d ≤ 100000")},
 "total-harga": {
  "en": ("Total Price", "An item costs **h** rupiah. A customer takes **n** of the same item. Print the amount to pay.",
         "One line with h and n.", "One number: the total price.", "500 ≤ h ≤ 50000, 1 ≤ n ≤ 100"),
  "ar": ("السعر الإجمالي", "سلعة سعرها **h** روبية. أخذ مشترٍ **n** قطعة منها. اطبع المبلغ الواجب دفعه.",
         "سطر واحد فيه h و n.", "عدد واحد: السعر الإجمالي.", "500 ≤ h ≤ 50000، 1 ≤ n ≤ 100")},
 "kode-huruf": {
  "en": ("The Code of a Letter", "Inside a computer every letter is stored as a number (its ASCII code). The letter `A` is stored as 65, `B` as 66, and `a` as 97.\n\n"
         "Read one letter, then print the number that stores it.\n\nHint: keep the letter in a variable of type `char`, then convert it to `int` before printing.",
         "One letter, upper or lower case.", "One number: the code of that letter.", "A letter A–Z or a–z."),
  "ar": ("رمز الحرف", "داخل الحاسوب يُخزَّن كل حرف كعدد (رمز ASCII). الحرف `A` يُخزَّن 65 و `B` يُخزَّن 66 و `a` يُخزَّن 97.\n\n"
         "اقرأ حرفًا واحدًا ثم اطبع العدد الذي يخزّنه.\n\nتلميح: احفظ الحرف في متغير من النوع `char` ثم حوّله إلى `int` قبل الطباعة.",
         "حرف لاتيني واحد، كبير أو صغير.", "عدد واحد: رمز ذلك الحرف.", "حرف من A–Z أو a–z.")},
 "celsius-ke-fahrenheit": {
  "en": ("Celsius to Fahrenheit", "A temperature of **c** degrees Celsius equals **c × 9 / 5 + 32** degrees Fahrenheit.\n\n"
         "Read c (an integer) and print the temperature in Fahrenheit as a real number.\n\nCareful: 37 degrees Celsius is 98.6 degrees Fahrenheit, not 98.",
         "One integer c.", "One number: the temperature in Fahrenheit.", "−50 ≤ c ≤ 150"),
  "ar": ("من مئوي إلى فهرنهايت", "درجة حرارة **c** مئوية تساوي **c × 9 / 5 + 32** فهرنهايت.\n\n"
         "اقرأ c (عدد صحيح) واطبع الحرارة بالفهرنهايت كعدد عشري.\n\nانتبه: 37 درجة مئوية هي 98.6 فهرنهايت، لا 98.",
         "عدد صحيح واحد c.", "عدد واحد: الحرارة بالفهرنهايت.", "−50 ≤ c ≤ 150")},
 "potong-dan-bulatkan": {
  "en": ("Truncate and Round", "Given a positive real number **x**, print two integers:\n\n"
         "1. x **truncated**: its fraction is thrown away (7.98 becomes 7);\n2. x **rounded** to the nearest integer (7.98 becomes 8, and 7.50 becomes 8).\n\n"
         "Hint: converting a `double` to `int` throws away the fraction. What happens if 0.5 is added to x first?",
         "One real number x with two digits after the decimal point.", "First line: x truncated. Second line: x rounded.", "0 ≤ x < 1000"),
  "ar": ("القطع والتقريب", "إذا أُعطيت عددًا عشريًا موجبًا **x** فاطبع عددين صحيحين:\n\n"
         "1. x **مقطوعًا**: يُحذف جزؤه الكسري (7.98 يصبح 7)؛\n2. x **مقرَّبًا** إلى أقرب عدد صحيح (7.98 يصبح 8، و 7.50 يصبح 8).\n\n"
         "تلميح: تحويل `double` إلى `int` يحذف الجزء الكسري. ماذا يحدث لو أُضيف 0.5 إلى x قبل ذلك؟",
         "عدد عشري واحد x برقمين بعد الفاصلة العشرية.", "السطر الأول: x مقطوعًا. السطر الثاني: x مقرَّبًا.", "0 ≤ x < 1000")},
 "huruf-berikutnya": {
  "en": ("The Next Letter", "Read one lowercase letter, then print the letter that comes right after it in the alphabet. After `a` comes `b`, after `k` comes `l`.\n\n"
         "Hint: a `char` can be added to a number. The result is a number, so convert it back to `char` before printing.",
         "One lowercase letter.", "One letter.", "A letter from a to y (not z)."),
  "ar": ("الحرف التالي", "اقرأ حرفًا لاتينيًا صغيرًا ثم اطبع الحرف الذي يليه مباشرة في الأبجدية. بعد `a` يأتي `b` وبعد `k` يأتي `l`.\n\n"
         "تلميح: يمكن جمع `char` مع عدد. الناتج عدد، فحوّله إلى `char` من جديد قبل الطباعة.",
         "حرف لاتيني صغير واحد.", "حرف واحد.", "حرف من a إلى y (لا z).")},
 "digit-terakhir": {
  "en": ("The Last Digit", "Given an integer **n**, print its last digit, then the number without its last digit.\n\nExample: for 5827 the last digit is 7 and the rest is 582.",
         "One integer n.", "First line: the last digit of n. Second line: n without its last digit.", "10 ≤ n ≤ 999999"),
  "ar": ("الرقم الأخير", "إذا أُعطيت عددًا صحيحًا **n** فاطبع رقمه الأخير ثم العدد بلا رقمه الأخير.\n\nمثال: في 5827 الرقم الأخير 7 والباقي 582.",
         "عدد صحيح واحد n.", "السطر الأول: الرقم الأخير من n. السطر الثاني: n بلا رقمه الأخير.", "10 ≤ n ≤ 999999")},
 "jam-menit-detik": {
  "en": ("Hours, Minutes, Seconds", "A stopwatch shows **d** seconds. Write it as hours, minutes and seconds.\n\nExample: 3725 seconds is 1 hour 2 minutes 5 seconds.",
         "One integer d.", "One line with three numbers separated by spaces: hours, minutes, seconds.", "0 ≤ d < 86400"),
  "ar": ("ساعات ودقائق وثوانٍ", "ساعة إيقاف تُظهر **d** ثانية. اكتبها بالساعات والدقائق والثواني.\n\nمثال: 3725 ثانية هي ساعة واحدة ودقيقتان و 5 ثوانٍ.",
         "عدد صحيح واحد d.", "سطر واحد فيه ثلاثة أعداد تفصل بينها مسافات: الساعات ثم الدقائق ثم الثواني.", "0 ≤ d < 86400")},
 "rata-rata-dua-bilangan": {
  "en": ("Average of Two Numbers", "Given two integers, print their average as a real number.\n\nThe average of 3 and 4 is 3.5, not 3.",
         "One line with two integers.", "One number: their average.", "0 ≤ both numbers ≤ 1000"),
  "ar": ("متوسط عددين", "إذا أُعطيت عددين صحيحين فاطبع متوسطهما كعدد عشري.\n\nمتوسط 3 و 4 هو 3.5، لا 3.",
         "سطر واحد فيه عددان صحيحان.", "عدد واحد: متوسطهما.", "0 ≤ العددان ≤ 1000")},
 "luas-segitiga": {
  "en": ("Area of a Triangle", "A triangle has base **a** and height **t**, both integers. Its area is **a × t / 2**.\n\nPrint the area. A triangle with base 3 and height 5 has area 7.5.",
         "One line with a and t.", "One number: the area of the triangle.", "1 ≤ a, t ≤ 300"),
  "ar": ("مساحة المثلث", "مثلث قاعدته **a** وارتفاعه **t**، وكلاهما عدد صحيح. مساحته **a × t / 2**.\n\nاطبع المساحة. مثلث قاعدته 3 وارتفاعه 5 مساحته 7.5.",
         "سطر واحد فيه a و t.", "عدد واحد: مساحة المثلث.", "1 ≤ a, t ≤ 300")},
 "jumlah-tiga-digit": {
  "en": ("Sum of Three Digits", "Given a three-digit number, print the sum of its three digits.\n\nExample: for 472 the answer is 4 + 7 + 2 = 13.\n\n"
         "Hint: `/` and `%` with 10 and 100 can separate the digits of a number.",
         "One three-digit integer.", "One number: the sum of its digits.", "100 ≤ n ≤ 999"),
  "ar": ("مجموع ثلاثة أرقام", "إذا أُعطيت عددًا من ثلاثة أرقام فاطبع مجموع أرقامه الثلاثة.\n\nمثال: في 472 الجواب 4 + 7 + 2 = 13.\n\n"
         "تلميح: `/` و `%` مع 10 و 100 يفصلان أرقام العدد.",
         "عدد صحيح واحد من ثلاثة أرقام.", "عدد واحد: مجموع أرقامه.", "100 ≤ n ≤ 999")},
 "balik-tiga-digit": {
  "en": ("Reverse Three Digits", "Given a three-digit number, print the number with its digits in reverse order.\n\n"
         "Example: 472 becomes 274. The result is still a number, so 120 becomes 21 (not 021).",
         "One three-digit integer.", "One number: the number with its digits reversed.", "100 ≤ n ≤ 999"),
  "ar": ("عكس ثلاثة أرقام", "إذا أُعطيت عددًا من ثلاثة أرقام فاطبع العدد بعد عكس ترتيب أرقامه.\n\n"
         "مثال: 472 يصبح 274. الناتج يبقى عددًا، لذلك 120 يصبح 21 (لا 021).",
         "عدد صحيح واحد من ثلاثة أرقام.", "عدد واحد: العدد بأرقام معكوسة.", "100 ≤ n ≤ 999")},
 "pecahan-uang": {
  "en": ("Banknotes", "A cash machine pays out **n** rupiah using **as few notes as possible**. The notes available are "
         "100000, 50000, 20000, 10000, 5000, 2000 and 1000.\n\nPrint how many notes of each kind come out, starting with the largest.\n\n"
         "Example: 188000 = 1 × 100000 + 1 × 50000 + 1 × 20000 + 1 × 10000 + 1 × 5000 + 1 × 2000 + 1 × 1000.",
         "One integer n, a multiple of 1000.", "Seven lines: the number of notes of 100000, 50000, 20000, 10000, 5000, 2000, 1000.", "1000 ≤ n ≤ 5000000"),
  "ar": ("الأوراق النقدية", "يصرف صرّاف آلي مبلغ **n** روبية **بأقل عدد ممكن من الأوراق**. الفئات المتاحة: "
         "100000 و 50000 و 20000 و 10000 و 5000 و 2000 و 1000.\n\nاطبع عدد الأوراق من كل فئة، بدءًا من الأكبر.\n\n"
         "مثال: 188000 = 1 × 100000 + 1 × 50000 + 1 × 20000 + 1 × 10000 + 1 × 5000 + 1 × 2000 + 1 × 1000.",
         "عدد صحيح واحد n من مضاعفات 1000.", "سبعة أسطر: عدد أوراق 100000 ثم 50000 ثم 20000 ثم 10000 ثم 5000 ثم 2000 ثم 1000.", "1000 ≤ n ≤ 5000000")},
 "huruf-kecil-ke-besar": {
  "en": ("Lowercase to Uppercase", "Read one lowercase letter and print its uppercase form: `a` becomes `A`, `m` becomes `M`.\n\n"
         "Hint: the codes of the lowercase letters run in order from `'a'` to `'z'`, and those of the uppercase letters from `'A'` to `'Z'`. "
         "The distance of a lowercase letter from `'a'` equals the distance of its uppercase letter from `'A'`.",
         "One lowercase letter.", "One uppercase letter.", "A letter from a to z."),
  "ar": ("من حرف صغير إلى حرف كبير", "اقرأ حرفًا لاتينيًا صغيرًا واطبع صورته الكبيرة: `a` يصبح `A` و `m` يصبح `M`.\n\n"
         "تلميح: رموز الحروف الصغيرة متتالية من `'a'` إلى `'z'`، ورموز الحروف الكبيرة متتالية من `'A'` إلى `'Z'`. "
         "بُعد الحرف الصغير عن `'a'` يساوي بُعد حرفه الكبير عن `'A'`.",
         "حرف لاتيني صغير واحد.", "حرف لاتيني كبير واحد.", "حرف من a إلى z.")},
 "lama-perjalanan": {
  "en": ("Journey Time", "A train leaves at **j1:m1** and arrives at **j2:m2** on the same day. Print how long the journey takes, in hours and minutes.\n\n"
         "Example: leaving at 07:45 and arriving at 10:20 is a journey of 2 hours 35 minutes.\n\nHint: turn both times into minutes since midnight.",
         "One line with four integers: j1 m1 j2 m2.", "One line: the number of hours, one space, then the number of minutes.",
         "0 ≤ j1, j2 ≤ 23; 0 ≤ m1, m2 ≤ 59; the arrival is not earlier than the departure."),
  "ar": ("مدة الرحلة", "ينطلق قطار في الساعة **j1:m1** ويصل في الساعة **j2:m2** من اليوم نفسه. اطبع مدة الرحلة بالساعات والدقائق.\n\n"
         "مثال: الانطلاق 07:45 والوصول 10:20 يعني رحلة مدتها ساعتان و 35 دقيقة.\n\nتلميح: حوّل الوقتين إلى دقائق منذ منتصف الليل.",
         "سطر واحد فيه أربعة أعداد صحيحة: j1 m1 j2 m2.", "سطر واحد: عدد الساعات ثم مسافة ثم عدد الدقائق.",
         "0 ≤ j1, j2 ≤ 23؛ 0 ≤ m1, m2 ≤ 59؛ وقت الوصول ليس قبل وقت الانطلاق.")},
 "persentase-benar": {
  "en": ("Percentage Correct", "In a quiz a student answered **b** of **t** questions correctly. Print the percentage of correct answers as a real number.\n\n"
         "Example: 3 correct out of 8 questions is 37.5 percent.\n\nBe careful when dividing two integers.",
         "One line with b and t.", "One number: the percentage of correct answers.", "0 ≤ b ≤ t; t is one of 4, 5, 8, 10, 20, 25, 40, 50."),
  "ar": ("نسبة الإجابات الصحيحة", "في اختبار قصير أجاب طالب إجابة صحيحة عن **b** من **t** سؤالًا. اطبع النسبة المئوية للإجابات الصحيحة كعدد عشري.\n\n"
         "مثال: 3 إجابات صحيحة من 8 أسئلة تساوي 37.5 بالمئة.\n\nانتبه عند قسمة عددين صحيحين.",
         "سطر واحد فيه b و t.", "عدد واحد: النسبة المئوية للإجابات الصحيحة.", "0 ≤ b ≤ t؛ t أحد الأعداد 4، 5، 8، 10، 20، 25، 40، 50.")},
 "hari-jam-menit-detik": {
  "en": ("Days, Hours, Minutes, Seconds", "A server has been running for **d** seconds. Write that time in days, hours, minutes and seconds.\n\n"
         "Mind the limit of d: it can be far larger than what fits in an `int`.",
         "One integer d.", "One line with four numbers separated by spaces: days, hours, minutes, seconds.", "0 ≤ d ≤ 10¹²"),
  "ar": ("أيام وساعات ودقائق وثوانٍ", "خادم يعمل منذ **d** ثانية. اكتب هذه المدة بالأيام والساعات والدقائق والثواني.\n\n"
         "انتبه إلى حدّ d: قد يكون أكبر بكثير مما يتّسع له `int`.",
         "عدد صحيح واحد d.", "سطر واحد فيه أربعة أعداد تفصل بينها مسافات: الأيام ثم الساعات ثم الدقائق ثم الثواني.", "0 ≤ d ≤ 10¹²")},
 "hasil-kali-besar": {
  "en": ("A Large Product", "Given two integers **a** and **b**, print **a × b**.\n\n"
         "Both numbers fit in an `int`, but their product may not. The product of two `int` values is computed as an `int`, "
         "even if it is then stored in a larger variable — so one of them must be converted first.",
         "One line with a and b.", "One number: a × b.", "1 ≤ a, b ≤ 10⁹"),
  "ar": ("حاصل ضرب كبير", "إذا أُعطيت عددين صحيحين **a** و **b** فاطبع **a × b**.\n\n"
         "كلا العددين يتّسع له `int`، لكن حاصل ضربهما قد لا يتّسع. حاصل ضرب قيمتين من النوع `int` يُحسب كـ `int` "
         "حتى لو خُزِّن بعد ذلك في متغير أكبر — لذلك يجب تحويل أحدهما أولًا.",
         "سطر واحد فيه a و b.", "عدد واحد: a × b.", "1 ≤ a, b ≤ 10⁹")},
 "sandi-geser": {
  "en": ("Shift Cipher", "A simple cipher shifts an uppercase letter **k** steps along the alphabet. After `Z` it wraps around to `A`.\n\n"
         "Example: `A` shifted by 3 becomes `D`; `Y` shifted by 3 becomes `B`; `C` shifted by 26 stays `C`.\n\n"
         "Read one uppercase letter and k, then print the shifted letter.\n\n"
         "Hint: first compute the position of the letter (A = 0, B = 1, …, Z = 25). The `%` operator makes the count return to 0 after 25.",
         "One line with one uppercase letter and an integer k, separated by one space.", "One uppercase letter.", "A letter A–Z; 0 ≤ k ≤ 1000"),
  "ar": ("شيفرة الإزاحة", "شيفرة بسيطة تُزيح الحرف اللاتيني الكبير **k** خطوة في الأبجدية. بعد `Z` نعود إلى `A`.\n\n"
         "مثال: `A` بإزاحة 3 يصبح `D`؛ `Y` بإزاحة 3 يصبح `B`؛ `C` بإزاحة 26 يبقى `C`.\n\n"
         "اقرأ حرفًا كبيرًا واحدًا و k ثم اطبع الحرف بعد الإزاحة.\n\n"
         "تلميح: احسب أولًا ترتيب الحرف (A = 0، B = 1، …، Z = 25). العامل `%` يعيد العدّ إلى 0 بعد 25.",
         "سطر واحد فيه حرف لاتيني كبير وعدد صحيح k تفصل بينهما مسافة واحدة.", "حرف لاتيني كبير واحد.", "حرف من A–Z؛ 0 ≤ k ≤ 1000")},
 "jam-selesai": {
  "en": ("Finishing Time", "A process starts at **j:m** and runs for **d** minutes. It may run past midnight, even for days.\n\n"
         "Print how many midnights pass, then the hour and minute at which the process finishes.\n\n"
         "Example: starting at 22:30 and running 200 minutes, it finishes at 01:50 the next day: `1 1 50`.",
         "One line with three integers: j m d.", "One line with three numbers separated by spaces: midnights passed, finishing hour, finishing minute.",
         "0 ≤ j ≤ 23; 0 ≤ m ≤ 59; 0 ≤ d ≤ 100000"),
  "ar": ("وقت الانتهاء", "تبدأ عملية في الساعة **j:m** وتستمر **d** دقيقة. قد تتجاوز منتصف الليل، بل قد تستمر أيامًا.\n\n"
         "اطبع عدد مرات تجاوز منتصف الليل، ثم ساعة ودقيقة انتهاء العملية.\n\n"
         "مثال: تبدأ 22:30 وتستمر 200 دقيقة فتنتهي 01:50 من اليوم التالي: `1 1 50`.",
         "سطر واحد فيه ثلاثة أعداد صحيحة: j m d.", "سطر واحد فيه ثلاثة أعداد تفصل بينها مسافات: مرات تجاوز منتصف الليل، ساعة الانتهاء، دقيقة الانتهاء.",
         "0 ≤ j ≤ 23؛ 0 ≤ m ≤ 59؛ 0 ≤ d ≤ 100000")},
 "nilai-akhir": {
  "en": ("Final Grade", "The final grade of a course is computed from three integer marks: assignments (weight 20%), midterm (weight 30%) and final exam (weight 50%).\n\n"
         "Print the final grade as a real number.\n\nExample: assignments 80, midterm 75, final exam 91 give 16 + 22.5 + 45.5 = 84.",
         "One line with three integers: assignments, midterm, final exam.", "One number: the final grade.", "0 ≤ each mark ≤ 100"),
  "ar": ("الدرجة النهائية", "تُحسب الدرجة النهائية لمقرر من ثلاث درجات صحيحة: الواجبات (وزنها 20%) والامتحان النصفي (30%) والامتحان النهائي (50%).\n\n"
         "اطبع الدرجة النهائية كعدد عشري.\n\nمثال: الواجبات 80 والنصفي 75 والنهائي 91 تعطي 16 + 22.5 + 45.5 = 84.",
         "سطر واحد فيه ثلاثة أعداد صحيحة: الواجبات، النصفي، النهائي.", "عدد واحد: الدرجة النهائية.", "0 ≤ كل درجة ≤ 100")},
 "besar-dan-kecil": {
  "en": ("The Larger and the Smaller", "Given two integers **a** and **b**, print the larger one, then the smaller one.\n\n"
         "Branching has not been taught yet, but this can be solved with arithmetic alone. Notice: "
         "the sum of the two numbers plus their distance (**|a − b|**) equals twice the larger number.\n\n"
         "The function `abs` from `<cstdlib>` gives the absolute value of an integer.",
         "One line with a and b.", "First line: the larger number. Second line: the smaller one.", "−1000000 ≤ a, b ≤ 1000000"),
  "ar": ("الأكبر والأصغر", "إذا أُعطيت عددين صحيحين **a** و **b** فاطبع الأكبر ثم الأصغر.\n\n"
         "لم ندرس التفرّع بعد، لكن المسألة تُحلّ بالحساب وحده. لاحظ: "
         "مجموع العددين مضافًا إليه المسافة بينهما (**|a − b|**) يساوي ضعف العدد الأكبر.\n\n"
         "الدالة `abs` من `<cstdlib>` تعطي القيمة المطلقة لعدد صحيح.",
         "سطر واحد فيه a و b.", "السطر الأول: العدد الأكبر. السطر الثاني: الأصغر.", "−1000000 ≤ a, b ≤ 1000000")},
 "tukar-digit-ujung": {
  "en": ("Swap the End Digits", "Given a five-digit number, swap its first digit with its last digit and print the number that results.\n\n"
         "Example: 52819 becomes 92815. The three middle digits do not change.",
         "One five-digit integer whose last digit is not 0.", "One five-digit number.", "10000 ≤ n ≤ 99999; n is not divisible by 10."),
  "ar": ("تبديل الرقمين الطرفيين", "إذا أُعطيت عددًا من خمسة أرقام فبدِّل رقمه الأول برقمه الأخير واطبع العدد الناتج.\n\n"
         "مثال: 52819 يصبح 92815. الأرقام الثلاثة الوسطى لا تتغير.",
         "عدد صحيح واحد من خمسة أرقام رقمه الأخير ليس 0.", "عدد واحد من خمسة أرقام.", "10000 ≤ n ≤ 99999؛ n لا يقبل القسمة على 10.")},
 "jam-mundur": {
  "en": ("The Clock Backwards", "It is now **j:m**. What time was it **k** minutes ago? The value of k can be very large: days, even years ago.\n\n"
         "Print the hour and the minute.\n\nExample: 100 minutes before 01:20 is 23:40 on the previous day.\n\n"
         "Careful: in C++, `%` on a negative number gives a negative result or zero, not a positive remainder.",
         "One line with three integers: j m k.", "One line: the hour, one space, then the minute.", "0 ≤ j ≤ 23; 0 ≤ m ≤ 59; 0 ≤ k ≤ 10⁹"),
  "ar": ("الساعة إلى الوراء", "الساعة الآن **j:m**. كم كانت الساعة قبل **k** دقيقة؟ قد تكون k كبيرة جدًا: أيامًا، بل سنوات.\n\n"
         "اطبع الساعة والدقيقة.\n\nمثال: قبل 100 دقيقة من 01:20 كانت الساعة 23:40 من اليوم السابق.\n\n"
         "انتبه: في ++C يعطي `%` مع عدد سالب ناتجًا سالبًا أو صفرًا، لا باقيًا موجبًا.",
         "سطر واحد فيه ثلاثة أعداد صحيحة: j m k.", "سطر واحد: الساعة ثم مسافة ثم الدقيقة.", "0 ≤ j ≤ 23؛ 0 ≤ m ≤ 59؛ 0 ≤ k ≤ 10⁹")},
 "nilai-tengah": {
  "en": ("The Middle Value", "Given three integers, print their **middle value**: the number that is in the middle when the three are sorted.\n\n"
         "Example: the middle value of 7, 2, 5 is 5. The middle value of 4, 9, 4 is 4.\n\n"
         "Branching has not been taught yet. Think: the sum of the three numbers minus the largest and the smallest is the middle value. "
         "The larger of two numbers can be computed from their sum and their distance (see the problem *The Larger and the Smaller*).",
         "One line with three integers.", "One number: the middle value.", "−1000000 ≤ each number ≤ 1000000"),
  "ar": ("القيمة الوسطى", "إذا أُعطيت ثلاثة أعداد صحيحة فاطبع **قيمتها الوسطى**: العدد الذي يقع في الوسط عند ترتيب الثلاثة.\n\n"
         "مثال: القيمة الوسطى لـ 7 و 2 و 5 هي 5. القيمة الوسطى لـ 4 و 9 و 4 هي 4.\n\n"
         "لم ندرس التفرّع بعد. فكّر: مجموع الأعداد الثلاثة مطروحًا منه الأكبر والأصغر هو القيمة الوسطى. "
         "يمكن حساب الأكبر بين عددين من مجموعهما والمسافة بينهما (انظر مسألة *الأكبر والأصغر*).",
         "سطر واحد فيه ثلاثة أعداد صحيحة.", "عدد واحد: القيمة الوسطى.", "−1000000 ≤ كل عدد ≤ 1000000")},
 "kode-tiga-huruf": {
  "en": ("Three-Letter Code", "A warehouse gives every shelf a code of three uppercase letters, counting like an odometer: "
         "shelf 0 has code `AAA`, shelf 1 `AAB`, …, shelf 25 `AAZ`, shelf 26 `ABA`, and so on up to `ZZZ`.\n\n"
         "Given the shelf number **n**, print its code.\n\nExample: shelf 28 has code `ABC`, and shelf 17575 has code `ZZZ`.\n\n"
         "Hint: this is like separating the digits of a number, but every \"digit\" has 26 possibilities instead of 10.",
         "One integer n.", "Three uppercase letters without spaces.", "0 ≤ n ≤ 17575"),
  "ar": ("رمز من ثلاثة أحرف", "يعطي مستودع كل رفّ رمزًا من ثلاثة أحرف لاتينية كبيرة، تتوالى كعدّاد المسافات: "
         "الرف 0 رمزه `AAA` والرف 1 رمزه `AAB` … والرف 25 رمزه `AAZ` والرف 26 رمزه `ABA` وهكذا حتى `ZZZ`.\n\n"
         "إذا أُعطيت رقم الرف **n** فاطبع رمزه.\n\nمثال: الرف 28 رمزه `ABC` والرف 17575 رمزه `ZZZ`.\n\n"
         "تلميح: هذا يشبه فصل أرقام عدد، لكن لكل «رقم» 26 احتمالًا بدل 10.",
         "عدد صحيح واحد n.", "ثلاثة أحرف لاتينية كبيرة بلا مسافات.", "0 ≤ n ≤ 17575")},
}
