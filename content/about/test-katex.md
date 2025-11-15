---
Title: KaTeX Test-Seite
Author: System
Tag: test, mathe, latex
---

# KaTeX Test-Seite

Diese Seite testet die KaTeX-Integration in StaticMD.

## Inline-Mathematik

Hier ist eine einfache Formel: $c = \sqrt{a^2 + b^2}$ mitten im Text.   

Die Euler'sche Identität $e^{i\pi} + 1 = 0$ ist eine der schönsten Formeln der Mathematik.   

Quadratische Gleichung: $x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a}$.  

## Block-Mathematik

Die Gauß'sche Normalverteilung:

$$
f(x) = \frac{1}{\sigma\sqrt{2\pi}} e^{-\frac{1}{2}\left(\frac{x-\mu}{\sigma}\right)^2}
$$

Ein komplexeres Beispiel mit mehreren Zeilen:

$$
\begin{aligned}
\nabla \times \vec{\mathbf{B}} -\, \frac1c\, \frac{\partial\vec{\mathbf{E}}}{\partial t} &= \frac{4\pi}{c}\vec{\mathbf{j}} \\
\nabla \cdot \vec{\mathbf{E}} &= 4 \pi \rho \\
\nabla \times \vec{\mathbf{E}}\, +\, \frac1c\, \frac{\partial\vec{\mathbf{B}}}{\partial t} &= \vec{\mathbf{0}} \\
\nabla \cdot \vec{\mathbf{B}} &= 0
\end{aligned}
$$

## Matrix-Beispiel

$$
\begin{pmatrix}
a & b \\
c & d
\end{pmatrix}
\begin{pmatrix}
x \\
y
\end{pmatrix}
=
\begin{pmatrix}
ax + by \\
cx + dy
\end{pmatrix}
$$

## Integration mit Markdown

**Fett:** $\mathbf{F} = ma$

*Kursiv:* $\int_0^\infty e^{-x^2} dx = \frac{\sqrt{\pi}}{2}$

Liste mit Mathematik:
- Erste Formel: $\sin^2(x) + \cos^2(x) = 1$
- Zweite Formel: $\lim_{n \to \infty} \left(1 + \frac{1}{n}\right)^n = e$
- Dritte Formel: $\sum_{n=1}^{\infty} \frac{1}{n^2} = \frac{\pi^2}{6}$

## Code vs. Mathematik

In Code-Blöcken sollten Dollar-Zeichen nicht als LaTeX interpretiert werden:

```javascript
const price = $100;
const tax = $price * 0.19;
```

Aber außerhalb von Code: $f(x) = x^2 + 2x + 1$

## Große Formeln

$$
\zeta(s) = \sum_{n=1}^{\infty} \frac{1}{n^s} = \prod_{p \text{ prime}} \frac{1}{1-p^{-s}}
$$

$$
\int_{-\infty}^{\infty} \int_{-\infty}^{\infty} \int_{-\infty}^{\infty} \psi^*(x,y,z) \hat{H} \psi(x,y,z) \, dx \, dy \, dz = E
$$