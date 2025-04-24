-- Update GradeName format from G(n)-MZHY to MZHY-G(n)
UPDATE grade
SET GradeName = CONCAT(SUBSTRING(GradeName, INSTR(GradeName, '-') + 1), '-', SUBSTRING(GradeName, 1, INSTR(GradeName, '-') - 1))
WHERE GradeName LIKE 'G%-%ZHY';