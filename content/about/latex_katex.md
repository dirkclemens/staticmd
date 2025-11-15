---
Title: LaTex / KaTeX
Author: System
Tag: test, math, latex
Visibility: public
---

# KaTeX Overview

This page tests the KaTeX integration in StaticMD.

## Inline Mathematics

Here is a simple formula: $c = \sqrt{a^2 + b^2}$ in the middle of text.   

Euler's identity $e^{i\pi} + 1 = 0$ is one of the most beautiful formulas in mathematics.   

Quadratic equation: $x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a}$.  

## Block Mathematics

The Gaussian normal distribution:

$$
f(x) = \frac{1}{\sigma\sqrt{2\pi}} e^{-\frac{1}{2}\left(\frac{x-\mu}{\sigma}\right)^2}
$$

A more complex example with multiple lines:

$$
\begin{aligned}
\nabla \times \vec{\mathbf{B}} -\, \frac1c\, \frac{\partial\vec{\mathbf{E}}}{\partial t} &= \frac{4\pi}{c}\vec{\mathbf{j}} \\
\nabla \cdot \vec{\mathbf{E}} &= 4 \pi \rho \\
\nabla \times \vec{\mathbf{E}}\, +\, \frac1c\, \frac{\partial\vec{\mathbf{B}}}{\partial t} &= \vec{\mathbf{0}} \\
\nabla \cdot \vec{\mathbf{B}} &= 0
\end{aligned}
$$

## Matrix Example

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

## Integration with Markdown

**Bold:** $\mathbf{F} = ma$

*Italic:* $\int_0^\infty e^{-x^2} dx = \frac{\sqrt{\pi}}{2}$

List with mathematics:
- First formula: $\sin^2(x) + \cos^2(x) = 1$
- Second formula: $\lim_{n \to \infty} \left(1 + \frac{1}{n}\right)^n = e$
- Third formula: $\sum_{n=1}^{\infty} \frac{1}{n^2} = \frac{\pi^2}{6}$

## Code vs. Mathematics

In code blocks, dollar signs should not be interpreted as LaTeX:

```javascript
const price = $100;
const tax = $price * 0.19;
```

But outside of code: $f(x) = x^2 + 2x + 1$

## Large Formulas

$$
\zeta(s) = \sum_{n=1}^{\infty} \frac{1}{n^s} = \prod_{p \text{ prime}} \frac{1}{1-p^{-s}}
$$

$$
\int_{-\infty}^{\infty} \int_{-\infty}^{\infty} \int_{-\infty}^{\infty} \psi^*(x,y,z) \hat{H} \psi(x,y,z) \, dx \, dy \, dz = E
$$