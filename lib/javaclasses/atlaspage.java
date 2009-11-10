import java.awt.*;
import java.applet.*;

public class atlaspage extends Applet
{ public void paint(Graphics g)
    {  String declRadStr=getParameter("decl");
       String raRadStr=getParameter("ra");
       g.drawString("Drawing atlas page for decl coordinates:",100,100);
       g.drawString(declRadStr, 150, 100);
       g.drawString("Drawing atlas page for ra   coordinates:",100,130);
       g.drawString(raRadStr, 150, 130)
       g.setColor(Color.BLACK);
       g.drawLine(10,10,300,10);
    }
}
